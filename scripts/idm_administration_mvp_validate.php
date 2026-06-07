<?php

declare(strict_types=1);

/**
 * idM Identity Administration MVP — runtime validation script.
 *
 * Usage:
 *   IDM_BASE_URL=https://idm.oerse-kippies.nl IDM_API_KEY=<key> php scripts/idm_administration_mvp_validate.php
 *
 * Optional: load config/env.versio when IDM_API_KEY is not set.
 */

$rootDir = dirname(__DIR__);
require $rootDir . '/src/Autoloader.php';
IdM\Autoloader::register($rootDir . '/src');

function loadApiKey(string $rootDir): string
{
    $fromEnv = getenv('IDM_API_KEY');
    if (is_string($fromEnv) && $fromEnv !== '') {
        return $fromEnv;
    }

    $envFile = $rootDir . '/config/env.versio';
    if (is_readable($envFile)) {
        $parsed = parse_ini_file($envFile, false, INI_SCANNER_RAW);
        if (is_array($parsed) && !empty($parsed['IDM_API_KEY'])) {
            return (string) $parsed['IDM_API_KEY'];
        }
    }

    throw new RuntimeException('IDM_API_KEY is required (env or config/env.versio)');
}

function uuidV4(): string
{
    $bytes = random_bytes(16);
    $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
    $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
    $hex = bin2hex($bytes);

    return sprintf(
        '%s-%s-%s-%s-%s',
        substr($hex, 0, 8),
        substr($hex, 8, 4),
        substr($hex, 12, 4),
        substr($hex, 16, 4),
        substr($hex, 20, 12)
    );
}

/** @return array{status:int, body:array<string,mixed>|null, correlationId:?string, headers:array<string,string>} */
function request(
    string $method,
    string $baseUrl,
    string $path,
    string $apiKey,
    ?array $jsonBody = null,
    ?string $correlationId = null,
    bool $commL = false
): array {
    $correlationId ??= uuidV4();
    $url = rtrim($baseUrl, '/') . $path;
    $headerLines = [
        'Accept: application/json',
        'X-Correlation-Id: ' . $correlationId,
    ];
    if ($commL) {
        $headerLines[] = 'X-Source-Module: communicationLayer';
    } else {
        $headerLines[] = 'X-Api-Key: ' . $apiKey;
        $headerLines[] = 'X-Actor-Type: SYSTEM';
    }

    $content = null;
    if ($jsonBody !== null) {
        $headerLines[] = 'Content-Type: application/json';
        $content = json_encode($jsonBody, JSON_THROW_ON_ERROR);
    }

    $context = stream_context_create([
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headerLines),
            'content' => $content,
            'ignore_errors' => true,
            'timeout' => 30,
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]);

    $bodyText = file_get_contents($url, false, $context);
    if ($bodyText === false) {
        throw new RuntimeException("HTTP request failed for {$method} {$url}");
    }

    $status = 0;
    $responseHeaders = [];
    if (isset($http_response_header) && is_array($http_response_header)) {
        foreach ($http_response_header as $line) {
            if (preg_match('#^HTTP/\S+\s+(\d+)#', $line, $matches)) {
                $status = (int) $matches[1];
            }
            if (str_contains($line, ':')) {
                [$name, $value] = explode(':', $line, 2);
                $responseHeaders[strtolower(trim($name))] = trim($value);
            }
        }
    }

    $decoded = $bodyText !== '' ? json_decode($bodyText, true) : null;

    return [
        'status' => $status,
        'body' => is_array($decoded) ? $decoded : null,
        'correlationId' => $responseHeaders['x-correlation-id'] ?? $correlationId,
        'headers' => $responseHeaders,
    ];
}

function assertStatus(array $result, int $expected, string $step): void
{
    if ($result['status'] !== $expected) {
        $body = json_encode($result['body'], JSON_THROW_ON_ERROR);
        throw new RuntimeException("{$step}: expected HTTP {$expected}, got {$result['status']}: {$body}");
    }
    fwrite(STDOUT, "[PASS] {$step} (HTTP {$expected})\n");
}

$baseUrl = getenv('IDM_BASE_URL') ?: 'https://idm.oerse-kippies.nl';
$apiKey = loadApiKey($rootDir);
$suffix = substr(bin2hex(random_bytes(4)), 0, 8);
$runCorrelation = uuidV4();

$evidence = [
    'runCorrelationId' => $runCorrelation,
    'baseUrl' => $baseUrl,
    'capturedAt' => gmdate('c'),
    'steps' => [],
];

fwrite(STDOUT, "idM Administration MVP validation\n");
fwrite(STDOUT, "Target: {$baseUrl}\n");
fwrite(STDOUT, "Run correlation: {$runCorrelation}\n\n");

// 1. Create user
$userCorr = uuidV4();
$userPayload = [
    'username' => 'admin.mvp.' . $suffix,
    'displayName' => 'Admin MVP User ' . $suffix,
    'email' => 'admin.mvp.' . $suffix . '@example.test',
];
$createUser = request('POST', $baseUrl, '/v1/users', $apiKey, $userPayload, $userCorr);
assertStatus($createUser, 201, 'Create user');
$userId = (string) ($createUser['body']['userId'] ?? '');
if ($userId === '') {
    throw new RuntimeException('Create user: missing userId');
}
$evidence['steps']['createUser'] = ['correlationId' => $userCorr, 'response' => $createUser['body']];

// 2. Enable user (PENDING -> ACTIVE for login)
$enableCorr = uuidV4();
$enableUser = request('POST', $baseUrl, "/v1/users/{$userId}/enable", $apiKey, null, $enableCorr);
assertStatus($enableUser, 200, 'Enable user');
$evidence['steps']['enableUser'] = ['correlationId' => $enableCorr, 'response' => $enableUser['body']];

// 3. List users
$listCorr = uuidV4();
$listUsers = request('GET', $baseUrl, '/v1/users', $apiKey, null, $listCorr);
assertStatus($listUsers, 200, 'List users');
$evidence['steps']['listUsers'] = ['correlationId' => $listCorr, 'response' => $listUsers['body']];

// 4. View user
$viewCorr = uuidV4();
$viewUser = request('GET', $baseUrl, "/v1/users/{$userId}", $apiKey, null, $viewCorr);
assertStatus($viewUser, 200, 'View user');
$evidence['steps']['viewUser'] = ['correlationId' => $viewCorr, 'response' => $viewUser['body']];

// 5. Update user
$updateCorr = uuidV4();
$updateUser = request('PATCH', $baseUrl, "/v1/users/{$userId}", $apiKey, [
    'displayName' => 'Admin MVP User ' . $suffix . ' (updated)',
], $updateCorr);
assertStatus($updateUser, 200, 'Update user');
$evidence['steps']['updateUser'] = ['correlationId' => $updateCorr, 'response' => $updateUser['body']];

// 6. Create role
$roleCorr = uuidV4();
$createRole = request('POST', $baseUrl, '/v1/roles', $apiKey, [
    'roleCode' => 'admin.mvp.' . $suffix,
    'roleName' => 'Admin MVP Role ' . $suffix,
    'description' => 'CCM administration MVP validation role',
], $roleCorr);
assertStatus($createRole, 201, 'Create role');
$roleId = (string) ($createRole['body']['roleId'] ?? '');
$evidence['steps']['createRole'] = ['correlationId' => $roleCorr, 'response' => $createRole['body']];

// 7. Create permission
$permCorr = uuidV4();
$createPerm = request('POST', $baseUrl, '/v1/permissions', $apiKey, [
    'permissionCode' => 'admin.mvp.' . $suffix,
    'permissionName' => 'Admin MVP Permission ' . $suffix,
    'description' => 'CCM administration MVP validation permission',
], $permCorr);
assertStatus($createPerm, 201, 'Create permission');
$permissionId = (string) ($createPerm['body']['permissionId'] ?? '');
$evidence['steps']['createPermission'] = ['correlationId' => $permCorr, 'response' => $createPerm['body']];

// 8. Update permission
$updatePermCorr = uuidV4();
$updatePerm = request('PATCH', $baseUrl, "/v1/permissions/{$permissionId}", $apiKey, [
    'permissionName' => 'Admin MVP Permission ' . $suffix . ' (updated)',
], $updatePermCorr);
assertStatus($updatePerm, 200, 'Update permission');
$evidence['steps']['updatePermission'] = ['correlationId' => $updatePermCorr, 'response' => $updatePerm['body']];

// 9. Assign role to user
$assignRoleCorr = uuidV4();
$assignRole = request('POST', $baseUrl, "/v1/users/{$userId}/roles/{$roleId}", $apiKey, null, $assignRoleCorr);
assertStatus($assignRole, 204, 'Assign role to user');
$evidence['steps']['assignRoleToUser'] = ['correlationId' => $assignRoleCorr, 'status' => 204];

// 10. Assign permission to role
$assignPermCorr = uuidV4();
$assignPerm = request('POST', $baseUrl, "/v1/roles/{$roleId}/permissions/{$permissionId}", $apiKey, null, $assignPermCorr);
assertStatus($assignPerm, 204, 'Assign permission to role');
$evidence['steps']['assignPermissionToRole'] = ['correlationId' => $assignPermCorr, 'status' => 204];

// 11. Audit record for create user
$auditCorr = uuidV4();
$audit = request('GET', $baseUrl, '/v1/audit-log?correlationId=' . urlencode($userCorr), $apiKey, null, $auditCorr);
assertStatus($audit, 200, 'Audit log by correlationId');
$evidence['steps']['auditLog'] = ['correlationId' => $auditCorr, 'response' => $audit['body']];

// 12. Actor context resolve for new user (commL headers)
$actorCorr = uuidV4();
$actorContext = request('POST', $baseUrl, '/v1/identity/actor-context', $apiKey, [
    'credentialType' => 'USER',
    'subjectHint' => $userId,
], $actorCorr, true);
assertStatus($actorContext, 200, 'Actor context resolve (new user)');
$evidence['steps']['actorContextResolve'] = ['correlationId' => $actorCorr, 'response' => $actorContext['body']];

// 13. Lock / unlock cycle
$lockCorr = uuidV4();
$lockUser = request('POST', $baseUrl, "/v1/users/{$userId}/lock", $apiKey, null, $lockCorr);
assertStatus($lockUser, 200, 'Lock user');
$unlockCorr = uuidV4();
$unlockUser = request('POST', $baseUrl, "/v1/users/{$userId}/unlock", $apiKey, null, $unlockCorr);
assertStatus($unlockUser, 200, 'Unlock user');

// 14. Disable role
$disableRoleCorr = uuidV4();
$disableRole = request('POST', $baseUrl, "/v1/roles/{$roleId}/disable", $apiKey, null, $disableRoleCorr);
assertStatus($disableRole, 200, 'Disable role');

$outFile = $rootDir . '/runtime/evidence/idm-administration-mvp-capture.json';
file_put_contents($outFile, json_encode($evidence, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . PHP_EOL);

fwrite(STDOUT, "\nSUMMARY: pass=14 fail=0\n");
fwrite(STDOUT, "Evidence written: runtime/evidence/idm-administration-mvp-capture.json\n");
fwrite(STDOUT, "Created userId: {$userId}\n");
fwrite(STDOUT, "roleId: {$roleId}\n");
fwrite(STDOUT, "permissionId: {$permissionId}\n");
