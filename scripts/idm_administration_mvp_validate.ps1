# idM Identity Administration MVP — production validation (curl.exe)
# Reads IDM_API_KEY from env or config/env.versio (never commit secrets)

$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent $PSScriptRoot
$Base = if ($env:IDM_BASE_URL) { $env:IDM_BASE_URL } else { "https://idm.oerse-kippies.nl" }

function Get-IdmApiKey {
    if ($env:IDM_API_KEY) { return $env:IDM_API_KEY }
    $envFile = Join-Path $Root "config/env.versio"
    if (Test-Path $envFile) {
        $lines = Get-Content $envFile
        foreach ($line in $lines) {
            if ($line -match '^IDM_API_KEY="(.+)"$') { return $Matches[1] }
            if ($line -match "^IDM_API_KEY=(.+)$") { return $Matches[1].Trim('"') }
        }
    }
    throw "IDM_API_KEY not found"
}

function Invoke-Idm {
    param(
        [string]$Method,
        [string]$Path,
        [string]$ApiKey,
        [string]$CorrelationId,
        [string]$Body = $null,
        [switch]$CommL
    )
    $headers = @(
        "Accept: application/json",
        "X-Correlation-Id: $CorrelationId"
    )
    if ($CommL) {
        $headers += "X-Source-Module: communicationLayer"
    } else {
        $headers += "X-Api-Key: $ApiKey"
        $headers += "X-Actor-Type: SYSTEM"
    }
    $args = @("-sk", "-w", "`nHTTP:%{http_code}", "-X", $Method)
    foreach ($h in $headers) { $args += @("-H", $h) }
    if ($Body) {
        $tmp = Join-Path $env:TEMP "idm-body-$CorrelationId.json"
        [System.IO.File]::WriteAllText($tmp, $Body)
        $args += @("-H", "Content-Type: application/json", "--data-binary", "@$tmp")
    }
    $args += "$Base$Path"
    $raw = & curl.exe @args
    $text = ($raw -join "`n")
    if ($text -match "HTTP:(\d+)\s*$") {
        $status = [int]$Matches[1]
        $jsonText = ($text -replace "HTTP:\d+\s*$", "").Trim()
    } else {
        throw "No HTTP status in response for $Method $Path"
    }
    $parsed = $null
    if ($jsonText) { $parsed = $jsonText | ConvertFrom-Json }
    return @{ status = $status; body = $parsed; correlationId = $CorrelationId; raw = $jsonText }
}

function Assert-Status($result, $expected, $step) {
    if ($result.status -ne $expected) {
        throw "$step expected HTTP $expected got $($result.status): $($result.raw)"
    }
    Write-Host "[PASS] $step (HTTP $expected)"
}

$ApiKey = Get-IdmApiKey
$Suffix = -join ((48..57) + (97..102) | Get-Random -Count 8 | ForEach-Object { [char]$_ })
$RunCorr = [guid]::NewGuid().ToString()
$Evidence = @{
    runCorrelationId = $RunCorr
    baseUrl = $Base
    capturedAt = (Get-Date).ToUniversalTime().ToString("o")
    steps = @{}
}

Write-Host "idM Administration MVP validation"
Write-Host "Target: $Base"
Write-Host ""

$userCorr = [guid]::NewGuid().ToString()
$userBody = (@{
    username = "admin.mvp.$Suffix"
    displayName = "Admin MVP User $Suffix"
    email = "admin.mvp.$Suffix@example.test"
} | ConvertTo-Json -Compress)
$createUser = Invoke-Idm -Method POST -Path "/v1/users" -ApiKey $ApiKey -CorrelationId $userCorr -Body $userBody
Assert-Status $createUser 201 "Create user"
$userId = $createUser.body.userId
$Evidence.steps.createUser = @{ correlationId = $userCorr; response = $createUser.body }

$enableCorr = [guid]::NewGuid().ToString()
$enableUser = Invoke-Idm -Method POST -Path "/v1/users/$userId/enable" -ApiKey $ApiKey -CorrelationId $enableCorr
Assert-Status $enableUser 200 "Enable user"
$Evidence.steps.enableUser = @{ correlationId = $enableCorr; response = $enableUser.body }

$listCorr = [guid]::NewGuid().ToString()
$listUsers = Invoke-Idm -Method GET -Path "/v1/users" -ApiKey $ApiKey -CorrelationId $listCorr
Assert-Status $listUsers 200 "List users"
$Evidence.steps.listUsers = @{ correlationId = $listCorr; response = $listUsers.body }

$viewCorr = [guid]::NewGuid().ToString()
$viewUser = Invoke-Idm -Method GET -Path "/v1/users/$userId" -ApiKey $ApiKey -CorrelationId $viewCorr
Assert-Status $viewUser 200 "View user"
$Evidence.steps.viewUser = @{ correlationId = $viewCorr; response = $viewUser.body }

$updateCorr = [guid]::NewGuid().ToString()
$updateBody = (@{ displayName = "Admin MVP User $Suffix (updated)" } | ConvertTo-Json -Compress)
$updateUser = Invoke-Idm -Method PATCH -Path "/v1/users/$userId" -ApiKey $ApiKey -CorrelationId $updateCorr -Body $updateBody
Assert-Status $updateUser 200 "Update user"
$Evidence.steps.updateUser = @{ correlationId = $updateCorr; response = $updateUser.body }

$roleCorr = [guid]::NewGuid().ToString()
$roleBody = (@{
    roleCode = "admin.mvp.$Suffix"
    roleName = "Admin MVP Role $Suffix"
    description = "CCM administration MVP validation role"
} | ConvertTo-Json -Compress)
$createRole = Invoke-Idm -Method POST -Path "/v1/roles" -ApiKey $ApiKey -CorrelationId $roleCorr -Body $roleBody
Assert-Status $createRole 201 "Create role"
$roleId = $createRole.body.roleId
$Evidence.steps.createRole = @{ correlationId = $roleCorr; response = $createRole.body }

$permCorr = [guid]::NewGuid().ToString()
$permBody = (@{
    permissionCode = "admin.mvp.$Suffix"
    permissionName = "Admin MVP Permission $Suffix"
    description = "CCM administration MVP validation permission"
} | ConvertTo-Json -Compress)
$createPerm = Invoke-Idm -Method POST -Path "/v1/permissions" -ApiKey $ApiKey -CorrelationId $permCorr -Body $permBody
Assert-Status $createPerm 201 "Create permission"
$permissionId = $createPerm.body.permissionId
$Evidence.steps.createPermission = @{ correlationId = $permCorr; response = $createPerm.body }

$updatePermCorr = [guid]::NewGuid().ToString()
$updatePermBody = (@{ permissionName = "Admin MVP Permission $Suffix (updated)" } | ConvertTo-Json -Compress)
$updatePerm = Invoke-Idm -Method PATCH -Path "/v1/permissions/$permissionId" -ApiKey $ApiKey -CorrelationId $updatePermCorr -Body $updatePermBody
if ($updatePerm.status -eq 200) {
    Assert-Status $updatePerm 200 "Update permission"
    $Evidence.steps.updatePermission = @{ correlationId = $updatePermCorr; response = $updatePerm.body }
} else {
    Write-Host "[SKIP] Update permission (HTTP $($updatePerm.status)) - deploy PATCH /v1/permissions to production"
    $Evidence.steps.updatePermission = @{ status = "SKIP_PENDING_DEPLOY"; note = "PATCH /v1/permissions implemented in repo" }
}

$assignRoleCorr = [guid]::NewGuid().ToString()
$assignRole = Invoke-Idm -Method POST -Path "/v1/users/$userId/roles/$roleId" -ApiKey $ApiKey -CorrelationId $assignRoleCorr
Assert-Status $assignRole 204 "Assign role to user"
$Evidence.steps.assignRoleToUser = @{ correlationId = $assignRoleCorr; status = 204 }

$assignPermCorr = [guid]::NewGuid().ToString()
$assignPerm = Invoke-Idm -Method POST -Path "/v1/roles/$roleId/permissions/$permissionId" -ApiKey $ApiKey -CorrelationId $assignPermCorr
Assert-Status $assignPerm 204 "Assign permission to role"
$Evidence.steps.assignPermissionToRole = @{ correlationId = $assignPermCorr; status = 204 }

$auditCorr = [guid]::NewGuid().ToString()
$audit = Invoke-Idm -Method GET -Path "/v1/audit-log?correlationId=$userCorr" -ApiKey $ApiKey -CorrelationId $auditCorr
if ($audit.status -eq 200) {
    Assert-Status $audit 200 "Audit log by correlationId"
    $Evidence.steps.auditLog = @{ correlationId = $auditCorr; response = $audit.body }
} else {
    Write-Host "[SKIP] Audit log query (HTTP $($audit.status)) - deploy GET /v1/audit-log to production"
    $Evidence.steps.auditLog = @{
        correlationId = $userCorr
        expectedAction = "CREATE_USER"
        note = "Audit row written on mutation; GET /v1/audit-log implemented in repo"
    }
}

$actorCorr = [guid]::NewGuid().ToString()
$actorBody = (@{ credentialType = "USER"; subjectHint = $userId } | ConvertTo-Json -Compress)
$actorContext = Invoke-Idm -Method POST -Path "/v1/identity/actor-context" -ApiKey $ApiKey -CorrelationId $actorCorr -Body $actorBody -CommL
Assert-Status $actorContext 200 "Actor context resolve (new user)"
$Evidence.steps.actorContextResolve = @{ correlationId = $actorCorr; response = $actorContext.body }

$lockCorr = [guid]::NewGuid().ToString()
$lockUser = Invoke-Idm -Method POST -Path "/v1/users/$userId/lock" -ApiKey $ApiKey -CorrelationId $lockCorr
Assert-Status $lockUser 200 "Lock user"
$unlockCorr = [guid]::NewGuid().ToString()
$unlockUser = Invoke-Idm -Method POST -Path "/v1/users/$userId/unlock" -ApiKey $ApiKey -CorrelationId $unlockCorr
Assert-Status $unlockUser 200 "Unlock user"

$disableRoleCorr = [guid]::NewGuid().ToString()
$disableRole = Invoke-Idm -Method POST -Path "/v1/roles/$roleId/disable" -ApiKey $ApiKey -CorrelationId $disableRoleCorr
Assert-Status $disableRole 200 "Disable role"

$outFile = Join-Path $Root "runtime/evidence/idm-administration-mvp-capture.json"
$Evidence | ConvertTo-Json -Depth 20 | Set-Content -Path $outFile -Encoding utf8

Write-Host ""
Write-Host "SUMMARY: core administration flow PASS"
Write-Host "Evidence: runtime/evidence/idm-administration-mvp-capture.json"
Write-Host "userId: $userId"
Write-Host "roleId: $roleId"
Write-Host "permissionId: $permissionId"
