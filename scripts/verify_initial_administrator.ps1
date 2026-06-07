$ErrorActionPreference = "Stop"
$root = Split-Path $PSScriptRoot -Parent
$log = Join-Path $root "runtime\evidence\initial-administrator-verify.log"
$commlBase = if ($env:COMML_BASE_URL) { $env:COMML_BASE_URL } else { "https://comml.oerse-kippies.nl" }
$serviceAccountId = "00000000-0000-4000-8000-000000000001"
$probeUserId = "bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb"
$capturePath = Join-Path $root "runtime\evidence\initial-administrator-capture.json"

if (-not (Test-Path $capturePath)) {
    throw "Run create_initial_administrator.ps1 first"
}
$capture = Get-Content $capturePath -Raw | ConvertFrom-Json
$adminUserId = [string]$capture.adminUserId

function Write-Log([string]$Message) {
    Add-Content -Path $log -Value $Message
    Write-Host $Message
}

function Invoke-CommlRoute {
    param([string]$ContractId, [hashtable]$Request, [string]$CorrelationId)
    $body = @{
        contractId = $ContractId
        sourceModule = "coPilotManagement"
        mode = "forwarding"
        correlationId = $CorrelationId
        request = $Request
    } | ConvertTo-Json -Depth 10 -Compress
    $resp = Invoke-WebRequest -Uri "$commlBase/api/route.php" -Method POST `
        -ContentType "application/json" `
        -Headers @{
            "X-Correlation-Id" = $CorrelationId
            "X-Actor-Type" = "SERVICE_ACCOUNT"
            "X-Actor-Id" = $serviceAccountId
        } `
        -Body $body -UseBasicParsing -TimeoutSec 45
    $json = $resp.Content | ConvertFrom-Json
    if (-not $json.success) { throw ($json.error.message) }
    if ([int]$json.data.targetStatus -notin 200, 201, 204) {
        throw "target status $($json.data.targetStatus) for $ContractId"
    }
    return $json.data.response
}

New-Item -ItemType Directory -Force -Path (Split-Path $log) | Out-Null
Remove-Item $log -ErrorAction SilentlyContinue
Write-Log "=== Initial Administrator Verification $(Get-Date -Format o) ==="

$failures = 0
function Assert([string]$Name, [scriptblock]$Block) {
    try {
        & $Block
        Write-Log "PASS: $Name"
    } catch {
        $script:failures++
        Write-Log "FAIL: $Name - $($_.Exception.Message)"
    }
}

Assert "admin user ACTIVE" {
    $user = Invoke-CommlRoute -ContractId "idM.users.get.v1" -CorrelationId ([Guid]::NewGuid().ToString()) -Request @{
        method = "GET"
        pathParams = @{ userId = $adminUserId }
    }
    if ([string]$user.status -ne "ACTIVE") { throw "status is $($user.status)" }
    if ([string]$user.username -ne "admin") { throw "username mismatch" }
}

Assert "admin actor context has admin role" {
    $ctx = Invoke-CommlRoute -ContractId "idM.actorContext.resolve.v1" -CorrelationId ([Guid]::NewGuid().ToString()) -Request @{
        method = "POST"
        body = @{
            credentialType = "USER"
            subjectHint = $adminUserId
            presentationSessionId = [Guid]::NewGuid().ToString()
        }
    }
    if ($ctx.roles -notcontains "admin") { throw "missing admin role" }
    if ($ctx.permissions -notcontains "copm.identity.admin") { throw "missing copm.identity.admin" }
}

Assert "probe user has no admin privileges" {
    $ctx = Invoke-CommlRoute -ContractId "idM.actorContext.resolve.v1" -CorrelationId ([Guid]::NewGuid().ToString()) -Request @{
        method = "POST"
        body = @{
            credentialType = "USER"
            subjectHint = $probeUserId
            presentationSessionId = [Guid]::NewGuid().ToString()
        }
    }
    if ($ctx.actorType -ne "USER") { throw "expected actorType USER" }
    if ($ctx.roles -contains "admin") { throw "probe has admin role" }
    if ($ctx.permissions -contains "copm.identity.admin") { throw "probe has admin permission" }
}

foreach ($contract in @("idM.users.list.v1", "idM.roles.list.v1", "idM.permissions.list.v1")) {
    Assert "administration access via $contract" {
        Invoke-CommlRoute -ContractId $contract -CorrelationId ([Guid]::NewGuid().ToString()) -Request @{ method = "GET" } | Out-Null
    }
}

Write-Log ""
Write-Log "Failures: $failures"
if ($failures -gt 0) { exit 1 }
Write-Log "ALL CHECKS PASSED"
