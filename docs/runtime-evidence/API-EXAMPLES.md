# idM Runtime API Examples

Status: MVP Runtime Evidence
API Version: v1

## Health (no API key)

```http
GET /v1/health HTTP/1.1
Host: example.invalid
X-Correlation-Id: 8f3b2f1a-6d4c-4b2e-9f0a-1c2d3e4f5a6b
```

```json
{
  "status": "healthy",
  "module": "identityManagement",
  "moduleCode": "idM",
  "version": "v1",
  "timestamp": "2026-06-05T12:00:00Z"
}
```

## Create User (mutation + audit)

```http
POST /v1/users HTTP/1.1
Host: example.invalid
Content-Type: application/json
X-Api-Key: <configured-api-key>
X-Correlation-Id: 9a4c3e2b-7e5d-4c3f-a1b2-3c4d5e6f7a8b
X-Actor-Type: SYSTEM

{
  "username": "jane.doe",
  "displayName": "Jane Doe",
  "email": "jane.doe@example.invalid"
}
```

Audit evidence: `idm_audit_log.action = CREATE_USER` with matching `correlationId`.

## Unauthorized

```http
GET /v1/users HTTP/1.1
Host: example.invalid
```

```json
{
  "error": {
    "errorCode": "UNAUTHORIZED",
    "errorMessage": "invalid or missing API key",
    "correlationId": "<generated-uuid-v4>",
    "timestamp": "2026-06-05T12:00:01Z"
  }
}
```

## Correlation Propagation

Every response includes header:

```text
X-Correlation-Id: <uuid-v4>
X-Api-Version: v1
```

## Runtime Code References

```text
src/Infrastructure/Correlation.php
src/Audit/AuditRepository.php
src/Application.php
public/api/index.php
```
