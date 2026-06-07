# PHASE9-IDM-READINESS

Date: 2026-06-07T09:30:00Z  
Repository: OerseKippies/identityManagement  
Target: https://idm.oerse-kippies.nl  
Authority: OK-Core / PAEP Phase 9

## Status

```text
READY FOR GOVM AUDIT: NO
Readiness: NOT READY
Audit Recommendation: BLOCKED
Risk: LOW (single config blocker)
```

## Mandatory Reading Compliance

| Document | Status |
|---|---|
| `OerseKippies/OK-Core/START-HERE.md` | READ |
| `implementation/PHASE9-PRODUCTION-READINESS.md` | READ |
| `governance/decisions/GD-2026-06-06-DEPLOYMENT-TARGET-FIRST.md` | READ |

## Validated

| Area | Result | Evidence |
|---|---|---|
| Runtime (PHP 8.3, document root) | PASS | Versio `public_html` → `public/api`; `/health` HTTP 200 |
| Database (MariaDB provisioned) | UNVERIFIED RUNTIME | DirectAdmin DB reported present; runtime auth fails |
| Health endpoint | PASS | `GET /health` → 200 |
| commL connectivity | PASS | `GET https://comml.oerse-kippies.nl/api/health.php` → 200 |
| actorContext | FAIL | `POST /v1/identity/actor-context` → 500 |
| users.list | FAIL | `GET /v1/identity/users` → 500 |

## Root Cause Analysis

### actorContext / users.list HTTP 500

Investigation on Versio (`vserver423.axc.eu`):

1. **PHP error / stack trace (CLI reproduction)**

```text
RuntimeException: Database connection failed: SQLSTATE[28000] [1045]
Access denied for user 'nol_module_idm'@'localhost' (using password: YES)
  at src/Infrastructure/Database.php:32
  via Application::ensureRouter() → buildRouter()
```

2. **Config loading**

Server `config/config.php` is identical to `config/config.example.php` (MD5 match). Placeholder values remain:

```text
database.password = replace-with-production-db-password
api.api_key       = replace-with-production-api-key
```

3. **env.versio**

`config/env.versio` is **missing** on Versio. `scripts/sync_versio_env.php` could not derive working credentials (revM password does not authenticate `nol_module_idm`).

4. **Routing / DI / queries**

Not reached — failure occurs at PDO connect before repository/query execution.

5. **Apache logs**

No module-specific error log under `~/domains/idm.oerse-kippies.nl/logs/`; failure surfaced via application JSON `INTERNAL_ERROR` and CLI stack trace.

### Database validation commands

```bash
php test-db.php
php scripts/migrate.php
```

Observed:

```text
test-db.php: Access denied for user 'nol_module_idm'@'localhost'
migrate.php: same PDO 1045 error
```

Table count could not be verified from runtime without valid credentials. Schema migration `001_initial_schema.sql` defines **11** idM tables (not 19).

## Remediation Applied (Repository)

| Change | Purpose |
|---|---|
| `Config::mergeVersioEnv()` | Load `config/env.versio` overrides on Versio |
| `config/env.versio.example` | Document required production secrets |
| `scripts/sync_versio_env.php` | Server-side env bootstrap (no secret output) |
| `scripts/phase9_validate_endpoints.sh` | Repeatable endpoint sweep |

## Required Operator Action (Versio)

1. Copy `config/env.versio.example` → `config/env.versio` on Versio.
2. Set `IDM_DB_PASSWORD` to the DirectAdmin password for user/database `nol_module_idm`.
3. Set `IDM_API_KEY` to production API key.
4. Run:

```bash
php scripts/sync_versio_env.php   # optional verify
php test-db.php
php scripts/migrate.php
php scripts/apply_copm_probe_seed.php
bash scripts/phase9_validate_endpoints.sh
```

## Open Findings

| Finding | Severity | govM Impact |
|---|---|---|
| TLS certificate CN=`*.axc.eu` (hostname mismatch) | DEFERRED | Documented open deployment finding — not a readiness blocker per PAEP guidance |
| Runtime DB credentials not synced to `env.versio` | HIGH | **Blocks** actorContext and users.list validation |

## Audit Recommendation

```text
PROCEED: NO
BLOCKED: YES — pending config/env.versio with valid MariaDB credentials on Versio
```

After `env.versio` is applied and endpoints return HTTP 200, re-run this package and set:

```text
Status: READY FOR GOVM AUDIT
Audit Recommendation: PROCEED
```

## Handover to govM

When unblocked, invoke:

```text
Repository: OerseKippies/governanceVerificationManagement
Opdracht: GOVM-PHASE9-IDM-READINESS-AUDIT
```

Expected post-remediation outcome: **READY FOR PRODUCTION** (TLS remains tracked as open finding).
