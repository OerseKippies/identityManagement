# idM Operations Model

Status: MVP Runtime
Classification: VERSIO_HOSTED

## Health

```text
GET /v1/health
```

Returns module code, API version, and timestamp. No authentication required.

## Configuration

Load from `config/config.php` (copy from `config/config.example.php`).

## Correlation

- Accept `X-Correlation-Id` (UUID v4) on every request.
- Echo `X-Correlation-Id` on every response.
- Persist `correlationId` on every audit record.

## Logging

- Business mutations: `idm_audit_log`
- Application errors: standard JSON error model with `correlationId`

## Scheduled Operations

Cron may be used post-MVP for token reference expiry and audit retention jobs. No daemon required for MVP runtime.

## Deployment Operations

```text
php scripts/migrate.php
HTTPS termination at Versio edge
Git-based deployment
```
