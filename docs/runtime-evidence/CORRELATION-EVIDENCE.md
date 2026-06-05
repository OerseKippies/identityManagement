# correlationId Runtime Evidence

Date: 2026-06-05
Result: PASS

## Implementation

| Layer | Path |
|---|---|
| Resolution | `src/Infrastructure/Correlation.php` |
| Request bootstrap | `public/api/index.php` — reads `X-Correlation-Id` |
| Response headers | `src/Http/Response.php` — echoes `X-Correlation-Id` |
| Audit persistence | `src/Audit/AuditRepository.php` — stores `correlationId` |
| Error payload | `src/Http/Response.php` — includes `correlationId` in error JSON |

## Format

UUID v4

## Unit Test Evidence

```text
D:\Programs\PHP\php.exe tests\run.php
```

```text
[PASS] Correlation resolves supplied valid header
[PASS] Correlation generates UUID when header missing
[PASS] Error response includes correlationId and errorCode
```

## Health Endpoint Evidence

Request:

```http
X-Correlation-Id: a1b2c3d4-e5f6-4789-a012-3456789abcde
```

Response header:

```text
X-Correlation-Id: a1b2c3d4-e5f6-4789-a012-3456789abcde
```

Reference: `docs/runtime-evidence/HEALTH-ENDPOINT-EVIDENCE.md`

## Database Schema

```text
idm_audit_log.correlationId CHAR(36) NOT NULL
```

Reference: `migrations/001_initial_schema.sql`
