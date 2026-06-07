# idM Audit Runtime Evidence

Status: MVP Runtime

## Implementation

- `src/Audit/AuditLogger.php` — audit write API
- `src/Audit/AuditRepository.php` — persistence to `idm_audit_log`
- `src/Domain/Service/*` — audit on every mutation inside transactions

## Correlation In Audit

Every audit row includes `correlationId` (UUID v4).

Schema: `migrations/001_initial_schema.sql` (`idm_audit_log.correlationId`)

## Sample Verification Query

```sql
SELECT auditId, entityType, entityId, action, correlationId, timestamp
FROM idm_audit_log
WHERE correlationId = '9a4c3e2b-7e5d-4c3f-a1b2-3c4d5e6f7a8b';
```

## Covered Mutations

```text
CREATE_USER, UPDATE_USER, DISABLE_USER, ENABLE_USER, LOCK_USER, UNLOCK_USER
CREATE_ROLE, UPDATE_ROLE, DISABLE_ROLE
CREATE_PERMISSION, UPDATE_PERMISSION
ASSIGN_ROLE, REMOVE_ROLE, ASSIGN_PERMISSION, REMOVE_PERMISSION
CREATE_SERVICE_ACCOUNT, UPDATE_SERVICE_ACCOUNT, DISABLE/ENABLE/LOCK/UNLOCK_SERVICE_ACCOUNT
CREATE_ACCESS_POLICY, UPDATE_ACCESS_POLICY, ACTIVATE_ACCESS_POLICY, RETIRE_ACCESS_POLICY
CREATE_TOKEN_REFERENCE, REVOKE_TOKEN_REFERENCE
```
