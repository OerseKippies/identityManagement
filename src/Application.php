<?php

declare(strict_types=1);

namespace IdM;

use IdM\Audit\AuditLogger;
use IdM\Audit\AuditRepository;
use IdM\Domain\Service\AccessPolicyService;
use IdM\Domain\Service\ActorContextService;
use IdM\Domain\Service\AssignmentService;
use IdM\Domain\Service\PermissionService;
use IdM\Domain\Service\RoleService;
use IdM\Domain\Service\ServiceAccountService;
use IdM\Domain\Service\TokenReferenceService;
use IdM\Domain\Service\UserService;
use IdM\Http\ApiException;
use IdM\Http\Request;
use IdM\Http\Response;
use IdM\Http\Router;
use IdM\Infrastructure\ActorContext;
use IdM\Infrastructure\Clock;
use IdM\Infrastructure\Config;
use IdM\Infrastructure\Correlation;
use IdM\Infrastructure\Database;
use IdM\Infrastructure\Uuid;
use IdM\Repository\AccessPolicyRepository;
use IdM\Repository\AssignmentRepository;
use IdM\Repository\PermissionRepository;
use IdM\Repository\RoleRepository;
use IdM\Repository\ServiceAccountRepository;
use IdM\Repository\TokenReferenceRepository;
use IdM\Repository\UserRepository;

final class Application
{
    private Config $config;
    private ?Database $database = null;
    private Clock $clock;
    private ?Router $router = null;

    public function __construct(string $configPath)
    {
        $this->config = Config::load($configPath);
        date_default_timezone_set($this->config->getString('app.timezone', 'UTC'));
        $this->clock = new Clock();
    }

    public function handle(Request $request): Response
    {
        try {
            if ($this->isHealthRequest($request)) {
                return Response::json(200, [
                    'status' => 'healthy',
                    'module' => 'identityManagement',
                    'moduleCode' => 'idM',
                    'version' => 'v1',
                    'timestamp' => $this->clock->nowIso8601(),
                ], $request->correlationId);
            }

            $this->ensureRouter();

            if (
                !$this->isCommLMediatedRequest($request)
                && $this->config->getBool('api.require_api_key', true)
            ) {
                $provided = $request->header('x-api-key');
                $expected = $this->config->getString('api.api_key');
                if ($provided === null || !hash_equals($expected, $provided)) {
                    return Response::error('UNAUTHORIZED', 'invalid or missing API key', 401, $request->correlationId, $this->clock);
                }
            }

            return $this->router->dispatch($request);
        } catch (ApiException $exception) {
            return Response::error($exception->errorCode, $exception->getMessage(), $exception->statusCode, $request->correlationId, $this->clock);
        } catch (\Throwable $exception) {
            return Response::error('INTERNAL_ERROR', 'unexpected server error', 500, $request->correlationId, $this->clock);
        }
    }

    private function isHealthRequest(Request $request): bool
    {
        return $request->method === 'GET' && ($request->path === '/v1/health' || $request->path === '/health');
    }

    private function isCommLMediatedRequest(Request $request): bool
    {
        $sourceModule = strtolower(trim((string) ($request->header('x-source-module') ?? '')));

        return $sourceModule === 'communicationlayer';
    }

    private function ensureRouter(): void
    {
        if ($this->router !== null) {
            return;
        }

        $this->database = new Database($this->config);
        $this->router = $this->buildRouter();
    }

    private function buildRouter(): Router
    {
        $auditLogger = new AuditLogger(new AuditRepository($this->database), $this->clock);

        $userService = new UserService($this->database, new UserRepository($this->database), $auditLogger, $this->clock);
        $roleService = new RoleService($this->database, new RoleRepository($this->database), $auditLogger, $this->clock);
        $permissionService = new PermissionService($this->database, new PermissionRepository($this->database), $auditLogger, $this->clock);
        $serviceAccountService = new ServiceAccountService($this->database, new ServiceAccountRepository($this->database), $auditLogger, $this->clock);
        $accessPolicyService = new AccessPolicyService($this->database, new AccessPolicyRepository($this->database), $auditLogger, $this->clock);
        $tokenReferenceService = new TokenReferenceService(
            $this->database,
            new TokenReferenceRepository($this->database),
            new UserRepository($this->database),
            new ServiceAccountRepository($this->database),
            $auditLogger,
            $this->clock
        );
        $assignmentService = new AssignmentService(
            $this->database,
            new AssignmentRepository($this->database),
            new UserRepository($this->database),
            new RoleRepository($this->database),
            new PermissionRepository($this->database),
            $auditLogger,
            $this->clock
        );
        $actorContextService = new ActorContextService(
            new UserRepository($this->database),
            new ServiceAccountRepository($this->database),
            new AssignmentRepository($this->database)
        );

        $router = new Router();
        $actor = static fn (Request $request): ActorContext => ActorContext::fromHeaders(
            $request->header('x-actor-type'),
            $request->header('x-actor-id')
        );

        $router->add('POST', '/v1/users', fn (Request $request): Response => Response::json(
            201,
            $userService->create($request->body ?? [], $actor($request), $request->correlationId),
            $request->correlationId
        ));
        $router->add('GET', '/v1/users', fn (Request $request): Response => Response::json(200, ['items' => $userService->list()], $request->correlationId));
        $router->add('GET', '/v1/identity/users', fn (Request $request): Response => Response::json(200, ['items' => $userService->list()], $request->correlationId));
        $router->add('POST', '/v1/identity/actor-context', fn (Request $request): Response => Response::json(
            200,
            $actorContextService->resolve($request->body ?? [], $request->correlationId),
            $request->correlationId
        ));
        $router->add('GET', '/v1/users/(?P<userId>[0-9a-f-]{36})', fn (Request $request, array $params): Response => Response::json(
            200,
            $userService->get($params['userId']),
            $request->correlationId
        ));
        $router->add('PATCH', '/v1/users/(?P<userId>[0-9a-f-]{36})', fn (Request $request, array $params): Response => Response::json(
            200,
            $userService->update($params['userId'], $request->body ?? [], $actor($request), $request->correlationId),
            $request->correlationId
        ));
        $router->add('POST', '/v1/users/(?P<userId>[0-9a-f-]{36})/disable', fn (Request $request, array $params): Response => Response::json(
            200,
            $userService->transition($params['userId'], 'DISABLED', 'DISABLE_USER', $actor($request), $request->correlationId),
            $request->correlationId
        ));
        $router->add('POST', '/v1/users/(?P<userId>[0-9a-f-]{36})/enable', fn (Request $request, array $params): Response => Response::json(
            200,
            $userService->transition($params['userId'], 'ACTIVE', 'ENABLE_USER', $actor($request), $request->correlationId),
            $request->correlationId
        ));
        $router->add('POST', '/v1/users/(?P<userId>[0-9a-f-]{36})/lock', fn (Request $request, array $params): Response => Response::json(
            200,
            $userService->transition($params['userId'], 'LOCKED', 'LOCK_USER', $actor($request), $request->correlationId),
            $request->correlationId
        ));
        $router->add('POST', '/v1/users/(?P<userId>[0-9a-f-]{36})/unlock', fn (Request $request, array $params): Response => Response::json(
            200,
            $userService->transition($params['userId'], 'ACTIVE', 'UNLOCK_USER', $actor($request), $request->correlationId),
            $request->correlationId
        ));

        $router->add('POST', '/v1/roles', fn (Request $request): Response => Response::json(
            201,
            $roleService->create($request->body ?? [], $actor($request), $request->correlationId),
            $request->correlationId
        ));
        $router->add('GET', '/v1/roles', fn (Request $request): Response => Response::json(200, ['items' => $roleService->list()], $request->correlationId));
        $router->add('GET', '/v1/roles/(?P<roleId>[0-9a-f-]{36})', fn (Request $request, array $params): Response => Response::json(
            200,
            $roleService->get($params['roleId']),
            $request->correlationId
        ));
        $router->add('PATCH', '/v1/roles/(?P<roleId>[0-9a-f-]{36})', fn (Request $request, array $params): Response => Response::json(
            200,
            $roleService->update($params['roleId'], $request->body ?? [], $actor($request), $request->correlationId),
            $request->correlationId
        ));
        $router->add('POST', '/v1/roles/(?P<roleId>[0-9a-f-]{36})/disable', fn (Request $request, array $params): Response => Response::json(
            200,
            $roleService->disable($params['roleId'], $actor($request), $request->correlationId),
            $request->correlationId
        ));

        $router->add('POST', '/v1/permissions', fn (Request $request): Response => Response::json(
            201,
            $permissionService->create($request->body ?? [], $actor($request), $request->correlationId),
            $request->correlationId
        ));
        $router->add('GET', '/v1/permissions', fn (Request $request): Response => Response::json(200, ['items' => $permissionService->list()], $request->correlationId));

        $router->add('GET', '/v1/permissions/(?P<permissionId>[0-9a-f-]{36})', fn (Request $request, array $params): Response => Response::json(
            200,
            $permissionService->get($params['permissionId']),
            $request->correlationId
        ));
        $router->add('PATCH', '/v1/permissions/(?P<permissionId>[0-9a-f-]{36})', fn (Request $request, array $params): Response => Response::json(
            200,
            $permissionService->update($params['permissionId'], $request->body ?? [], $actor($request), $request->correlationId),
            $request->correlationId
        ));

        $auditRepository = new AuditRepository($this->database);
        $router->add('GET', '/v1/audit-log', function (Request $request) use ($auditRepository): Response {
            $correlationFilter = isset($request->query['correlationId'])
                ? trim((string) $request->query['correlationId'])
                : '';
            if ($correlationFilter === '' || !Uuid::isValid($correlationFilter)) {
                throw new ApiException('VALIDATION_ERROR', 'correlationId query parameter is required and must be a valid UUID', 400);
            }

            return Response::json(200, ['items' => $auditRepository->findByCorrelationId(strtolower($correlationFilter))], $request->correlationId);
        });

        $router->add('POST', '/v1/users/(?P<userId>[0-9a-f-]{36})/roles/(?P<roleId>[0-9a-f-]{36})', function (Request $request, array $params) use ($assignmentService, $actor): Response {
            $assignmentService->assignRoleToUser($params['userId'], $params['roleId'], $actor($request), $request->correlationId);

            return Response::noContent($request->correlationId);
        });
        $router->add('DELETE', '/v1/users/(?P<userId>[0-9a-f-]{36})/roles/(?P<roleId>[0-9a-f-]{36})', function (Request $request, array $params) use ($assignmentService, $actor): Response {
            $assignmentService->removeRoleFromUser($params['userId'], $params['roleId'], $actor($request), $request->correlationId);

            return Response::noContent($request->correlationId);
        });
        $router->add('POST', '/v1/roles/(?P<roleId>[0-9a-f-]{36})/permissions/(?P<permissionId>[0-9a-f-]{36})', function (Request $request, array $params) use ($assignmentService, $actor): Response {
            $assignmentService->assignPermissionToRole($params['roleId'], $params['permissionId'], $actor($request), $request->correlationId);

            return Response::noContent($request->correlationId);
        });
        $router->add('DELETE', '/v1/roles/(?P<roleId>[0-9a-f-]{36})/permissions/(?P<permissionId>[0-9a-f-]{36})', function (Request $request, array $params) use ($assignmentService, $actor): Response {
            $assignmentService->removePermissionFromRole($params['roleId'], $params['permissionId'], $actor($request), $request->correlationId);

            return Response::noContent($request->correlationId);
        });

        $router->add('POST', '/v1/service-accounts', fn (Request $request): Response => Response::json(
            201,
            $serviceAccountService->create($request->body ?? [], $actor($request), $request->correlationId),
            $request->correlationId
        ));
        $router->add('GET', '/v1/service-accounts', fn (Request $request): Response => Response::json(200, ['items' => $serviceAccountService->list()], $request->correlationId));
        $router->add('GET', '/v1/service-accounts/(?P<serviceAccountId>[0-9a-f-]{36})', fn (Request $request, array $params): Response => Response::json(
            200,
            $serviceAccountService->get($params['serviceAccountId']),
            $request->correlationId
        ));
        $router->add('PATCH', '/v1/service-accounts/(?P<serviceAccountId>[0-9a-f-]{36})', fn (Request $request, array $params): Response => Response::json(
            200,
            $serviceAccountService->update($params['serviceAccountId'], $request->body ?? [], $actor($request), $request->correlationId),
            $request->correlationId
        ));
        $router->add('POST', '/v1/service-accounts/(?P<serviceAccountId>[0-9a-f-]{36})/disable', fn (Request $request, array $params): Response => Response::json(
            200,
            $serviceAccountService->transition($params['serviceAccountId'], 'DISABLED', 'DISABLE_SERVICE_ACCOUNT', $actor($request), $request->correlationId),
            $request->correlationId
        ));
        $router->add('POST', '/v1/service-accounts/(?P<serviceAccountId>[0-9a-f-]{36})/enable', fn (Request $request, array $params): Response => Response::json(
            200,
            $serviceAccountService->transition($params['serviceAccountId'], 'ACTIVE', 'ENABLE_SERVICE_ACCOUNT', $actor($request), $request->correlationId),
            $request->correlationId
        ));
        $router->add('POST', '/v1/service-accounts/(?P<serviceAccountId>[0-9a-f-]{36})/lock', fn (Request $request, array $params): Response => Response::json(
            200,
            $serviceAccountService->transition($params['serviceAccountId'], 'LOCKED', 'LOCK_SERVICE_ACCOUNT', $actor($request), $request->correlationId),
            $request->correlationId
        ));
        $router->add('POST', '/v1/service-accounts/(?P<serviceAccountId>[0-9a-f-]{36})/unlock', fn (Request $request, array $params): Response => Response::json(
            200,
            $serviceAccountService->transition($params['serviceAccountId'], 'ACTIVE', 'UNLOCK_SERVICE_ACCOUNT', $actor($request), $request->correlationId),
            $request->correlationId
        ));

        $router->add('POST', '/v1/access-policies', fn (Request $request): Response => Response::json(
            201,
            $accessPolicyService->create($request->body ?? [], $actor($request), $request->correlationId),
            $request->correlationId
        ));
        $router->add('GET', '/v1/access-policies', fn (Request $request): Response => Response::json(200, ['items' => $accessPolicyService->list()], $request->correlationId));
        $router->add('GET', '/v1/access-policies/(?P<policyId>[0-9a-f-]{36})', fn (Request $request, array $params): Response => Response::json(
            200,
            $accessPolicyService->get($params['policyId']),
            $request->correlationId
        ));
        $router->add('PATCH', '/v1/access-policies/(?P<policyId>[0-9a-f-]{36})', fn (Request $request, array $params): Response => Response::json(
            200,
            $accessPolicyService->update($params['policyId'], $request->body ?? [], $actor($request), $request->correlationId),
            $request->correlationId
        ));
        $router->add('POST', '/v1/access-policies/(?P<policyId>[0-9a-f-]{36})/activate', fn (Request $request, array $params): Response => Response::json(
            200,
            $accessPolicyService->activate($params['policyId'], $actor($request), $request->correlationId),
            $request->correlationId
        ));
        $router->add('POST', '/v1/access-policies/(?P<policyId>[0-9a-f-]{36})/retire', fn (Request $request, array $params): Response => Response::json(
            200,
            $accessPolicyService->retire($params['policyId'], $actor($request), $request->correlationId),
            $request->correlationId
        ));

        $router->add('POST', '/v1/token-references', fn (Request $request): Response => Response::json(
            201,
            $tokenReferenceService->create($request->body ?? [], $actor($request), $request->correlationId),
            $request->correlationId
        ));
        $router->add('GET', '/v1/token-references', fn (Request $request): Response => Response::json(200, ['items' => $tokenReferenceService->list()], $request->correlationId));
        $router->add('GET', '/v1/token-references/(?P<tokenReferenceId>[0-9a-f-]{36})', fn (Request $request, array $params): Response => Response::json(
            200,
            $tokenReferenceService->get($params['tokenReferenceId']),
            $request->correlationId
        ));
        $router->add('POST', '/v1/token-references/(?P<tokenReferenceId>[0-9a-f-]{36})/revoke', fn (Request $request, array $params): Response => Response::json(
            200,
            $tokenReferenceService->revoke($params['tokenReferenceId'], $actor($request), $request->correlationId),
            $request->correlationId
        ));

        return $router;
    }
}
