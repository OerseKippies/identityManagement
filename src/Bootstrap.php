<?php

declare(strict_types=1);

namespace Idm;

use Idm\Database\Connection;
use Idm\Database\MigrationRunner;
use Idm\Domain\AccessPolicyService;
use Idm\Domain\AuditService;
use Idm\Domain\PermissionService;
use Idm\Domain\RoleService;
use Idm\Domain\ServiceAccountService;
use Idm\Domain\TokenReferenceService;
use Idm\Domain\UserService;
use Idm\Http\Request;
use Idm\Http\Response;
use Idm\Http\Router;
use Idm\Repository\AccessPolicyRepository;
use Idm\Repository\AuditRepository;
use Idm\Repository\PermissionRepository;
use Idm\Repository\RoleRepository;
use Idm\Repository\ServiceAccountRepository;
use Idm\Repository\TokenReferenceRepository;
use Idm\Repository\UserRepository;
use Throwable;

final class Bootstrap
{
    public static function autoload(string $root): void
    {
        spl_autoload_register(static function (string $class) use ($root): void {
            $prefix = 'Idm\\';
            if (str_starts_with($class, $prefix) === false) {
                return;
            }
            $relative = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($prefix)));
            $file = $root . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $relative . '.php';
            if (is_file($file)) {
                require_once $file;
            }
        });
    }

    public static function run(array $config, string $root): void
    {
        $request = Request::fromGlobals();

        try {
            $pdo = Connection::create($config);
            if (($config['auto_migrate'] ?? false) === true) {
                (new MigrationRunner($pdo, $root . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations'))->run();
            }

            $audit = new AuditService(new AuditRepository($pdo));
            $users = new UserService(new UserRepository($pdo), $audit);
            $roles = new RoleService(new RoleRepository($pdo), $audit);
            $permissions = new PermissionService(new PermissionRepository($pdo), $audit);
            $serviceAccounts = new ServiceAccountService(new ServiceAccountRepository($pdo), $audit);
            $policies = new AccessPolicyService(new AccessPolicyRepository($pdo), $audit);
            $tokens = new TokenReferenceService(new TokenReferenceRepository($pdo), $audit);

            $router = new Router();
            self::routes($router, $users, $roles, $permissions, $serviceAccounts, $policies, $tokens);
            $router->dispatch($request);
        } catch (Throwable $error) {
            \Idm\Http\ErrorResponse::fromThrowable($error, $request->correlationId);
        }
    }

    private static function routes(
        Router $router,
        UserService $users,
        RoleService $roles,
        PermissionService $permissions,
        ServiceAccountService $serviceAccounts,
        AccessPolicyService $policies,
        TokenReferenceService $tokens
    ): void {
        $router->add('GET', '/health', static fn () => Response::json(['status' => 'ok', 'module' => 'identityManagement']));

        $router->add('POST', '/users', static fn (Request $r) => Router::handle(static fn () => Response::json(['data' => $users->create($r->body)], 201), $r));
        $router->add('GET', '/users', static fn () => Response::json(['data' => $users->list()]));
        $router->add('GET', '/users/{userId}', static fn (Request $r, array $p) => Router::handle(static fn () => Response::json(['data' => $users->get($p['userId'])]), $r));
        $router->add('PATCH', '/users/{userId}', static fn (Request $r, array $p) => Router::handle(static fn () => Response::json(['data' => $users->update($p['userId'], $r->body)]), $r));
        $router->add('POST', '/users/{userId}/disable', static fn (Request $r, array $p) => Router::handle(static fn () => Response::json(['data' => $users->transition($p['userId'], 'DISABLED', 'DISABLE')]), $r));
        $router->add('POST', '/users/{userId}/enable', static fn (Request $r, array $p) => Router::handle(static fn () => Response::json(['data' => $users->transition($p['userId'], 'ACTIVE', 'ENABLE')]), $r));
        $router->add('POST', '/users/{userId}/lock', static fn (Request $r, array $p) => Router::handle(static fn () => Response::json(['data' => $users->transition($p['userId'], 'LOCKED', 'LOCK')]), $r));
        $router->add('POST', '/users/{userId}/unlock', static fn (Request $r, array $p) => Router::handle(static fn () => Response::json(['data' => $users->transition($p['userId'], 'ACTIVE', 'UNLOCK')]), $r));

        $router->add('POST', '/roles', static fn (Request $r) => Router::handle(static fn () => Response::json(['data' => $roles->create($r->body)], 201), $r));
        $router->add('GET', '/roles', static fn () => Response::json(['data' => $roles->list()]));
        $router->add('GET', '/roles/{roleId}', static fn (Request $r, array $p) => Router::handle(static fn () => Response::json(['data' => $roles->get($p['roleId'])]), $r));
        $router->add('PATCH', '/roles/{roleId}', static fn (Request $r, array $p) => Router::handle(static fn () => Response::json(['data' => $roles->update($p['roleId'], $r->body)]), $r));
        $router->add('POST', '/roles/{roleId}/disable', static fn (Request $r, array $p) => Router::handle(static fn () => Response::json(['data' => $roles->disable($p['roleId'])]), $r));

        $router->add('POST', '/permissions', static fn (Request $r) => Router::handle(static fn () => Response::json(['data' => $permissions->create($r->body)], 201), $r));
        $router->add('GET', '/permissions', static fn () => Response::json(['data' => $permissions->list()]));
        $router->add('GET', '/permissions/{permissionId}', static fn (Request $r, array $p) => Router::handle(static fn () => Response::json(['data' => $permissions->get($p['permissionId'])]), $r));

        $router->add('POST', '/users/{userId}/roles/{roleId}', static fn (Request $r, array $p) => Router::handle(static function () use ($roles, $p): void { $roles->assignToUser($p['userId'], $p['roleId']); Response::noContent(); }, $r));
        $router->add('DELETE', '/users/{userId}/roles/{roleId}', static fn (Request $r, array $p) => Router::handle(static function () use ($roles, $p): void { $roles->removeFromUser($p['userId'], $p['roleId']); Response::noContent(); }, $r));
        $router->add('POST', '/roles/{roleId}/permissions/{permissionId}', static fn (Request $r, array $p) => Router::handle(static function () use ($permissions, $p): void { $permissions->assignToRole($p['roleId'], $p['permissionId']); Response::noContent(); }, $r));
        $router->add('DELETE', '/roles/{roleId}/permissions/{permissionId}', static fn (Request $r, array $p) => Router::handle(static function () use ($permissions, $p): void { $permissions->removeFromRole($p['roleId'], $p['permissionId']); Response::noContent(); }, $r));

        $router->add('POST', '/service-accounts', static fn (Request $r) => Router::handle(static fn () => Response::json(['data' => $serviceAccounts->create($r->body)], 201), $r));
        $router->add('GET', '/service-accounts', static fn () => Response::json(['data' => $serviceAccounts->list()]));
        $router->add('GET', '/service-accounts/{serviceAccountId}', static fn (Request $r, array $p) => Router::handle(static fn () => Response::json(['data' => $serviceAccounts->get($p['serviceAccountId'])]), $r));
        $router->add('PATCH', '/service-accounts/{serviceAccountId}', static fn (Request $r, array $p) => Router::handle(static fn () => Response::json(['data' => $serviceAccounts->update($p['serviceAccountId'], $r->body)]), $r));
        $router->add('POST', '/service-accounts/{serviceAccountId}/disable', static fn (Request $r, array $p) => Router::handle(static fn () => Response::json(['data' => $serviceAccounts->transition($p['serviceAccountId'], 'DISABLED', 'DISABLE')]), $r));
        $router->add('POST', '/service-accounts/{serviceAccountId}/enable', static fn (Request $r, array $p) => Router::handle(static fn () => Response::json(['data' => $serviceAccounts->transition($p['serviceAccountId'], 'ACTIVE', 'ENABLE')]), $r));
        $router->add('POST', '/service-accounts/{serviceAccountId}/lock', static fn (Request $r, array $p) => Router::handle(static fn () => Response::json(['data' => $serviceAccounts->transition($p['serviceAccountId'], 'LOCKED', 'LOCK')]), $r));
        $router->add('POST', '/service-accounts/{serviceAccountId}/unlock', static fn (Request $r, array $p) => Router::handle(static fn () => Response::json(['data' => $serviceAccounts->transition($p['serviceAccountId'], 'ACTIVE', 'UNLOCK')]), $r));

        $router->add('POST', '/access-policies', static fn (Request $r) => Router::handle(static fn () => Response::json(['data' => $policies->create($r->body)], 201), $r));
        $router->add('GET', '/access-policies', static fn () => Response::json(['data' => $policies->list()]));
        $router->add('GET', '/access-policies/{policyId}', static fn (Request $r, array $p) => Router::handle(static fn () => Response::json(['data' => $policies->get($p['policyId'])]), $r));
        $router->add('PATCH', '/access-policies/{policyId}', static fn (Request $r, array $p) => Router::handle(static fn () => Response::json(['data' => $policies->update($p['policyId'], $r->body)]), $r));
        $router->add('POST', '/access-policies/{policyId}/activate', static fn (Request $r, array $p) => Router::handle(static fn () => Response::json(['data' => $policies->transition($p['policyId'], 'ACTIVE', 'ACTIVATE_POLICY')]), $r));
        $router->add('POST', '/access-policies/{policyId}/retire', static fn (Request $r, array $p) => Router::handle(static fn () => Response::json(['data' => $policies->transition($p['policyId'], 'RETIRED', 'RETIRE_POLICY')]), $r));

        $router->add('POST', '/token-references', static fn (Request $r) => Router::handle(static fn () => Response::json(['data' => $tokens->create($r->body)], 201), $r));
        $router->add('GET', '/token-references', static fn () => Response::json(['data' => $tokens->list()]));
        $router->add('GET', '/token-references/{tokenReferenceId}', static fn (Request $r, array $p) => Router::handle(static fn () => Response::json(['data' => $tokens->get($p['tokenReferenceId'])]), $r));
        $router->add('POST', '/token-references/{tokenReferenceId}/revoke', static fn (Request $r, array $p) => Router::handle(static fn () => Response::json(['data' => $tokens->revoke($p['tokenReferenceId'])]), $r));
    }
}
