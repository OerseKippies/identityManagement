$ErrorActionPreference = "Stop"
$root = Split-Path $PSScriptRoot -Parent
$log = Join-Path $root "runtime\evidence\initial-administrator-create.log"
$commlBase = if ($env:COMML_BASE_URL) { $env:COMML_BASE_URL } else { "https://comml.oerse-kippies.nl" }
$serviceAccountId = "00000000-0000-4000-8000-000000000001"
$probeUserId = "bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb"

$AdminUsername = "admin"
$AdminEmail = "admin@oerse-kippies.nl"
$AdminDisplayName = "Platform Administrator"
$AdminRoleCode = "admin"
$AdminRoleName = "Platform Administrator"

$RequiredPermissions = @(
    "copm.identity.admin",
    "copm.diagnostics.view",
    "copm.admin",
    "copm.operator"
)

$state = @{
    adminUserId = $null
    adminRoleId = $null
    permissionIds = @{}
}

function Write-Log([string]$Message) {
    Add-Content -Path $log -Value $Message
    Write-Host $Message
}

function Invoke-CommlRoute {
    param(
        [string]$ContractId,
        [hashtable]$Request,
        [string]$CorrelationId
    )
    $body = @{
        contractId = $ContractId
        sourceModule = "coPilotManagement"
        mode = "forwarding"
        correlationId = $CorrelationId
        request = $Request
    } | ConvertTo-Json -Depth 10 -Compress
    try {
        $resp = Invoke-WebRequest -Uri "$commlBase/api/route.php" -Method POST `
            -ContentType "application/json" `
            -Headers @{
                "X-Correlation-Id" = $CorrelationId
                "X-Actor-Type" = "SERVICE_ACCOUNT"
                "X-Actor-Id" = $serviceAccountId
            } `
            -Body $body -UseBasicParsing -TimeoutSec 45
        $json = $resp.Content | ConvertFrom-Json
    } catch {
        if ($_.Exception.Response) {
            $reader = [System.IO.StreamReader]::new($_.Exception.Response.GetResponseStream())
            $detail = $reader.ReadToEnd()
            throw "HTTP error for $ContractId : $detail"
        }
        throw
    }
    if (-not $json.success) {
        throw ($json.error.message)
    }
    $status = [int]$json.data.targetStatus
    if ($status -notin 200, 201, 204, 409) {
        throw "target status $status for $ContractId"
    }
    return @{
        Status = $status
        Response = $json.data.response
    }
}

New-Item -ItemType Directory -Force -Path (Split-Path $log) | Out-Null
Remove-Item $log -ErrorAction SilentlyContinue
Write-Log "=== Initial Administrator Creation $(Get-Date -Format o) ==="
Write-Log "Path: copM consumer -> commL -> idM"
Write-Log "commL: $commlBase"
Write-Log ""

# --- Verify current authorization model ---
Write-Log "--- Current production authorization model ---"
$list = Invoke-CommlRoute -ContractId "idM.users.list.v1" -CorrelationId ([Guid]::NewGuid().ToString()) -Request @{ method = "GET" }
$users = @($list.Response.items)
Write-Log "Users in idM: $($users.Count)"
foreach ($u in $users) {
    Write-Log "  user: $($u.username) | $($u.displayName) | $($u.status) | $($u.userId)"
}

$probe = $users | Where-Object { $_.userId -eq $probeUserId } | Select-Object -First 1
if (-not $probe) { throw "Probe user not found in production" }
Write-Log "Probe user verified: $($probe.displayName) ($probeUserId)"

# --- Create or locate admin user ---
$existingAdmin = $users | Where-Object { $_.username -eq $AdminUsername } | Select-Object -First 1
if ($existingAdmin) {
    $state.adminUserId = [string]$existingAdmin.userId
    Write-Log "Admin user already exists: $($state.adminUserId)"
} else {
    $cid = [Guid]::NewGuid().ToString()
    $created = Invoke-CommlRoute -ContractId "idM.users.create.v1" -CorrelationId $cid -Request @{
        method = "POST"
        body = @{
            username = $AdminUsername
            displayName = $AdminDisplayName
            email = $AdminEmail
        }
    }
    $state.adminUserId = [string]$created.Response.userId
    Write-Log "Created admin user: $($state.adminUserId)"
}

# Enable admin user (required: new users start PENDING)
$adminUser = Invoke-CommlRoute -ContractId "idM.users.get.v1" -CorrelationId ([Guid]::NewGuid().ToString()) -Request @{
    method = "GET"
    pathParams = @{ userId = $state.adminUserId }
}
if ([string]$adminUser.Response.status -ne "ACTIVE") {
    Invoke-CommlRoute -ContractId "idM.users.enable.v1" -CorrelationId ([Guid]::NewGuid().ToString()) -Request @{
        method = "POST"
        pathParams = @{ userId = $state.adminUserId }
    } | Out-Null
    Write-Log "Admin user enabled to ACTIVE"
} else {
    Write-Log "Admin user already ACTIVE"
}

# --- Create or locate ADMIN role ---
$rolesList = Invoke-CommlRoute -ContractId "idM.roles.list.v1" -CorrelationId ([Guid]::NewGuid().ToString()) -Request @{ method = "GET" }
$roles = @($rolesList.Response.items)
$existingRole = $roles | Where-Object { $_.roleCode -eq $AdminRoleCode } | Select-Object -First 1
if ($existingRole) {
    $state.adminRoleId = [string]$existingRole.roleId
    Write-Log "Admin role already exists: $($state.adminRoleId) ($AdminRoleCode)"
} else {
    $createdRole = Invoke-CommlRoute -ContractId "idM.roles.create.v1" -CorrelationId ([Guid]::NewGuid().ToString()) -Request @{
        method = "POST"
        body = @{
            roleCode = $AdminRoleCode
            roleName = $AdminRoleName
            description = "Platform administrator - full copM identity and diagnostics access"
        }
    }
    $state.adminRoleId = [string]$createdRole.Response.roleId
    Write-Log "Created admin role: $($state.adminRoleId)"
}

# --- Create permissions and assign to role ---
$permList = Invoke-CommlRoute -ContractId "idM.permissions.list.v1" -CorrelationId ([Guid]::NewGuid().ToString()) -Request @{ method = "GET" }
$permissions = @($permList.Response.items)

foreach ($permCode in $RequiredPermissions) {
    $existingPerm = $permissions | Where-Object { $_.permissionCode -eq $permCode } | Select-Object -First 1
    if ($existingPerm) {
        $state.permissionIds[$permCode] = [string]$existingPerm.permissionId
        Write-Log "Permission exists: $permCode"
    } else {
        $createdPerm = Invoke-CommlRoute -ContractId "idM.permissions.create.v1" -CorrelationId ([Guid]::NewGuid().ToString()) -Request @{
            method = "POST"
            body = @{
                permissionCode = $permCode
                permissionName = $permCode
                description = "copM platform permission - $permCode"
            }
        }
        $state.permissionIds[$permCode] = [string]$createdPerm.Response.permissionId
        Write-Log "Created permission: $permCode"
    }

    try {
        Invoke-CommlRoute -ContractId "idM.roles.permissions.assign.v1" -CorrelationId ([Guid]::NewGuid().ToString()) -Request @{
            method = "POST"
            pathParams = @{
                roleId = $state.adminRoleId
                permissionId = $state.permissionIds[$permCode]
            }
        } | Out-Null
        Write-Log "Assigned permission $permCode to admin role"
    } catch {
        if ($_.Exception.Message -match "409|already assigned") {
            Write-Log "Permission $permCode already assigned to admin role"
        } else {
            throw
        }
    }
}

# --- Assign admin role to admin user ---
try {
    Invoke-CommlRoute -ContractId "idM.users.roles.assign.v1" -CorrelationId ([Guid]::NewGuid().ToString()) -Request @{
        method = "POST"
        pathParams = @{
            userId = $state.adminUserId
            roleId = $state.adminRoleId
        }
    } | Out-Null
    Write-Log "Assigned admin role to admin user"
} catch {
    if ($_.Exception.Message -match "409|already assigned") {
        Write-Log "Admin role already assigned to admin user"
    } else {
        throw
    }
}

# --- Verify actor contexts ---
function Get-ActorContext([string]$UserId) {
    $cid = [Guid]::NewGuid().ToString()
    $result = Invoke-CommlRoute -ContractId "idM.actorContext.resolve.v1" -CorrelationId $cid -Request @{
        method = "POST"
        body = @{
            credentialType = "USER"
            subjectHint = $UserId
            presentationSessionId = [Guid]::NewGuid().ToString()
        }
    }
    return $result.Response
}

$adminContext = Get-ActorContext -UserId $state.adminUserId
Write-Log ""
Write-Log "--- Admin actor context ---"
Write-Log "  actorType: $($adminContext.actorType)"
Write-Log "  roles: $($adminContext.roles -join ', ')"
Write-Log "  permissions: $($adminContext.permissions -join ', ')"

$probeContext = Get-ActorContext -UserId $probeUserId
Write-Log ""
Write-Log "--- Probe actor context ---"
Write-Log "  actorType: $($probeContext.actorType)"
Write-Log "  roles: $(if ($probeContext.roles) { $probeContext.roles -join ', ' } else { '(none)' })"
Write-Log "  permissions: $(if ($probeContext.permissions) { $probeContext.permissions -join ', ' } else { '(none)' })"

if ($adminContext.roles -notcontains $AdminRoleCode) {
    throw "Admin user missing role $AdminRoleCode"
}
foreach ($permCode in $RequiredPermissions) {
    if ($adminContext.permissions -notcontains $permCode) {
        throw "Admin user missing permission $permCode"
    }
}
if ($probeContext.roles -contains $AdminRoleCode) {
    throw "Probe user has admin role - privilege escalation detected"
}
foreach ($permCode in $RequiredPermissions) {
    if ($probeContext.permissions -contains $permCode) {
        throw "Probe user has admin permission $permCode - privilege escalation detected"
    }
}

# --- Verify administration API access (copM UI data path) ---
foreach ($contract in @("idM.users.list.v1", "idM.roles.list.v1", "idM.permissions.list.v1")) {
    Invoke-CommlRoute -ContractId $contract -CorrelationId ([Guid]::NewGuid().ToString()) -Request @{ method = "GET" } | Out-Null
    Write-Log "Administration list OK: $contract"
}

Write-Log ""
Write-Log "RESULT: SUCCESS"
Write-Log "adminUserId=$($state.adminUserId)"
Write-Log "adminRoleId=$($state.adminRoleId)"
Write-Log "loginIdentifier=$AdminUsername (or $AdminEmail)"

# Capture JSON for evidence
$capture = @{
    createdAt = (Get-Date).ToUniversalTime().ToString("o")
    adminUserId = $state.adminUserId
    adminRoleId = $state.adminRoleId
    username = $AdminUsername
    email = $AdminEmail
    displayName = $AdminDisplayName
    roleCode = $AdminRoleCode
    permissions = $RequiredPermissions
    adminActorContext = $adminContext
    probeActorContext = $probeContext
}
$capturePath = Join-Path $root "runtime\evidence\initial-administrator-capture.json"
$capture | ConvertTo-Json -Depth 8 | Set-Content -Path $capturePath -Encoding UTF8
Write-Log "Capture: $capturePath"
