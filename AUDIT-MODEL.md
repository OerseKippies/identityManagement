# idM Audit Model

Status: MVP Runtime

## Storage

Audit records are append-only rows in `idm_audit_log`.

## Required Fields

| Field | Notes |
|---|---|
| auditId | UUID |
| entityType | User, Role, Permission, ServiceAccount, AccessPolicy, TokenReference |
| entityId | idM entity UUID |
| action | Business mutation action name |
| actorType | USER, SERVICE_ACCOUNT, SYSTEM |
| actorId | Optional idM actor UUID |
| correlationId | UUID v4 per operation |
| timestamp | UTC datetime |
| detailsJson | Optional structured details |

## Mutation Coverage

Every business mutation in domain services writes an audit record before commit.

Runtime implementation: `src/Audit/AuditLogger.php`, `src/Domain/Service/*`

## Architecture Reference

Event matrix: `architecture/AUDIT-LOGGING.md`
