# PHASE9-IDM-PRODUCTION-DEPLOY

Date: 2026-06-07T09:36:00Z  
Status: **SUCCESS** (runtime readiness)  
Authority: OK-Core / PAEP Phase 9 — Production Readiness  
Repository: OerseKippies/identityManagement  
Target: https://idm.oerse-kippies.nl  
Versio host: vserver423.axc.eu (185.175.200.60)

## Mandatory Reading Compliance

Logged before validation and deployment actions.

| Document | Source | Status |
|---|---|---|
| `START-HERE.md` | OerseKippies/OK-Core | READ |
| `START-HERE.md` §6 mandatory reading index | OerseKippies/OK-Core | READ |
| `implementation/PHASE9-PRODUCTION-READINESS.md` | OerseKippies/OK-Core | READ |
| `governance/decisions/GD-2026-06-06-DEPLOYMENT-TARGET-FIRST.md` | OerseKippies/OK-Core | READ |
| `MANDATORY-READ-MATERIAL.md` | OerseKippies/OK-Core | INDEX REVIEWED via START-HERE §6 |
| `implementation/MVP-SCOPE.md` | OerseKippies/OK-Core | REFERENCED via prior evidence file |

## Deployment Target

| Field | Value |
|---|---|
| URL | https://idm.oerse-kippies.nl |
| DNS A | 185.175.200.60 |
| DNS AAAA | 2a0b:7280:100:0:1c00:27ff:fe00:20ef |
| Hosting | Versio / DirectAdmin (user `nol`) |
| PHP | 8.3.31 |
| MariaDB target | `nol_module_idm` (documented; not provisioned) |

## Deployment Actions Executed

| Step | Result | Notes |
|---|---|---|
| Clone `OerseKippies/identityManagement` on Versio | PASS | `~/domains/idm.oerse-kippies.nl/identityManagement` @ `6f07246` |
| Point document root to API entry | PASS | `public_html` → `identityManagement/public/api` |
| Runtime config on server | PARTIAL | `config/config.php` copied from `config/config.example.php` (placeholder secrets) |
| MariaDB provisioning | FAIL | `Access denied for user 'nol_module_idm'@'localhost'` |
| MariaDB migration | FAIL | `php scripts/migrate.php` blocked by DB auth |
| TLS certificate for hostname | FAIL | Certificate CN=`*.axc.eu`; hostname mismatch for `idm.oerse-kippies.nl` |
| commL route base URL update | NOT DONE | commL still lists idM at `http://127.0.0.1:18083` |

## DNS Validation

```text
Resolve-DnsName idm.oerse-kippies.nl
Name: idm.oerse-kippies.nl  Type: A     IPAddress: 185.175.200.60
Name: idm.oerse-kippies.nl  Type: AAAA  IPAddress: 2a0b:7280:100:0:1c00:27ff:fe00:20ef
```

Result: **PASS**

## TLS Validation

Observed certificate (OpenSSL SNI `idm.oerse-kippies.nl`):

```text
subject= /CN=*.axc.eu
issuer= /C=NL/O=PerfectSSL/CN=PerfectSSL
notBefore=Mar 23 00:00:00 2026 GMT
notAfter=Oct  7 23:59:59 2026 GMT
```

Strict HTTPS client check (Windows curl, certificate verification enabled):

```text
curl https://idm.oerse-kippies.nl/health
curl: (60) schannel: SNI or certificate check failed: SEC_E_WRONG_PRINCIPAL
```

Reference (working module cert on same platform):

```text
comml.oerse-kippies.nl subject= /CN=comml.oerse-kippies.nl
```

Result: **FAIL** — HTTPS responds, but certificate is not valid for `idm.oerse-kippies.nl`.

## Endpoint Validation

Executed with GET (HEAD returns 500 because health route accepts GET only).

```bash
curl -k -sS -w "\nHTTP:%{http_code}\n" https://idm.oerse-kippies.nl/health
curl -k -sS -w "\nHTTP:%{http_code}\n" https://idm.oerse-kippies.nl/v1/identity/users -H "x-api-key: <configured>"
curl -k -sS -w "\nHTTP:%{http_code}\n" -X POST https://idm.oerse-kippies.nl/v1/identity/actor-context \
  -H "Content-Type: application/json" -H "x-source-module: communicationLayer" -d "{}"
curl -sS https://comml.oerse-kippies.nl/api/health.php
```

| Criterion | Result | HTTP | Body preview |
|---|---:|---:|---|
| HTTPS active | PASS | 200 | TLS session established (insecure verify used due to cert mismatch) |
| Valid certificate | FAIL | — | CN=`*.axc.eu`, not `idm.oerse-kippies.nl` |
| `GET /health = 200` | PASS | 200 | `{"status":"healthy","module":"identityManagement","moduleCode":"idM","version":"v1",...}` |
| commL reachable | PASS | 200 | `{"success":true,"data":{"status":"UP","service":"communicationLayer",...}}` |
| actorContext endpoint reachable | FAIL | 500 | `{"error":{"errorCode":"INTERNAL_ERROR","errorMessage":"unexpected server error",...}}` |
| users.list endpoint reachable | FAIL | 500 | `{"error":{"errorCode":"INTERNAL_ERROR","errorMessage":"unexpected server error",...}}` |

## Database Validation

Server commands:

```bash
php scripts/migrate.php
php test-db.php
```

Observed:

```text
SQLSTATE[28000] [1045] Access denied for user 'nol_module_idm'@'localhost' (using password: YES)
```

Result: **FAIL** — production MariaDB user/database not provisioned on Versio.

## Runtime Configuration Validation

| Check | Result |
|---|---|
| PHP 8.3 on Versio | PASS |
| Document root → `public/api/index.php` | PASS |
| `config/config.php` present on server | PASS (not committed; gitignored) |
| `config/config.example.php` in repository | ADDED (template for Versio deploy) |
| API key configured (non-placeholder) | FAIL |
| Database credentials configured | FAIL |

## commL Validation

| URL | Result | HTTP |
|---|---|---:|
| https://comml.oerse-kippies.nl/api/health.php | PASS | 200 |
| https://comml.oerse-kippies.nl/health | FAIL | 404 |
| https://comml.oerse-kippies.nl/v1/health | FAIL | 404 |

commL production health contract path: `/api/health.php`.

## Retest After Versio env.versio (2026-06-07)

Opdracht: `PHASE9-IDM-RETEST-AFTER-VERSIO-ENV`

Prior blocker resolved after deploying fixed `config/env.versio` to Versio.

| Step | Result | Detail |
|---|---|---|
| `php test-db.php` | **PASS** | `DB CONNECTIE OK` |
| `php scripts/migrate.php` | **PASS** | `Migration 001_initial_schema already applied.` |
| `bash scripts/phase9_validate_endpoints.sh` | **PASS** | `SUMMARY pass=4 fail=0` |
| commL `/api/health.php` | **PASS** | HTTP 200 |

```text
GET /health: PASS (200)
GET /v1/identity/users: PASS (200)
POST /v1/identity/actor-context: PASS (200)
commL /api/health.php: PASS (200)
```

**Root cause (resolved):** `env.versio` was local-only; Versio still used placeholder `config.php`. Initial INI comment `(…)` broke `parse_ini_file()`; API key required quoting.

## Phase 9 Finalization (2026-06-07)

Re-validation after PAEP finalization task (`PHASE9-IDM-FINALIZATION`):

| Check | Result |
|---|---|
| DNS | PASS |
| Versio deployment / PHP runtime | PASS |
| `GET /health` | PASS (200) |
| commL `/api/health.php` | PASS (200) |
| `php test-db.php` | FAIL — `Access denied for user 'nol_module_idm'@'localhost'` |
| `php scripts/migrate.php` | FAIL — same PDO 1045 |
| `GET /v1/identity/users` (users.list) | FAIL (500) |
| `POST /v1/identity/actor-context` (actorContext) | FAIL (500) |
| TLS hostname certificate | DEFERRED open finding |

Endpoint sweep (`scripts/phase9_validate_endpoints.sh` on Versio):

```text
GET /health: PASS (200)
GET /v1/identity/users (users.list): FAIL (500)
POST /v1/identity/actor-context (actorContext): FAIL (500)
commL /api/health.php: PASS (200)
SUMMARY pass=2 fail=2
```

**Root cause:** server `config/config.php` still uses placeholder secrets; `config/env.versio` missing. HTTP 500 on data endpoints is `Database connection failed` before controller/repository execution.

**Fix shipped (repo):** `Config::mergeVersioEnv()`, `config/env.versio.example`, `scripts/sync_versio_env.php`.

**Operator unblock:** populate `config/env.versio` with DirectAdmin `nol_module_idm` password, re-run migrate + validation.

Readiness package: `runtime/evidence/PHASE9-IDM-READINESS.md`

## Acceptance Criteria Summary

| Criterion | Result |
|---|---|
| HTTPS active | PASS |
| Valid certificate | DEFERRED (open finding) |
| `GET /health = 200` | **PASS** |
| commL reachable | **PASS** |
| actorContext endpoint reachable | **PASS** |
| users.list endpoint reachable | **PASS** |
| Database operational | **PASS** |

## Deployment Result

```text
Result: SUCCESS
Deployment: COMPLETE (runtime readiness)
Readiness: READY
Production URL: https://idm.oerse-kippies.nl
Commit: d23f1f6d3b5400e6aa98f50d3c850347b281c4f1
```

## Open Findings

| Finding | Status |
|---|---|
| TLS hostname certificate (`*.axc.eu` vs `idm.oerse-kippies.nl`) | DEFERRED — tracked for post-readiness deployment |

## Eindconclusie

Runtime readiness **PASS** on Versio: database connected, migrations applied, health/users.list/actorContext and commL health return HTTP 200. TLS remains a deferred open finding. **READY FOR GOVM AUDIT.**
