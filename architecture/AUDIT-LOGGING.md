# idM Audit Logging

Status: MVP Ready

## Purpose

idM audit logging records security-relevant actions on idM-owned access identity entities.

Audit records are for idM access accountability only. They do not create IdentityAuditRecord ownership for canonical business-object identity.

## Audit Fields

| Field | Type | Notes |
|---|---|---|
| auditId | UUID | idM-owned audit identifier |
| entityType | string | User, Role, Permission, ServiceAccount, AccessPolicy, TokenReference, UserRole, RolePermission |
| entityId | UUID | idM-owned entity identifier |
| action | enum | Audit action |
| actorType | enum | USER, SERVICE_ACCOUNT, SYSTEM |
| actorId | UUID | idM actor identifier where known |
| timestamp | datetime | Action timestamp |
| detailsJson | JSON/text | Structured action details without foreign ownership |

## Actions

```text
CREATE
UPDATE
DELETE
DISABLE
ENABLE
LOCK
UNLOCK
ASSIGN_ROLE
REMOVE_ROLE
ASSIGN_PERMISSION
REMOVE_PERMISSION
CREATE_TOKEN_REFERENCE
REVOKE_TOKEN_REFERENCE
ACTIVATE_POLICY
RETIRE_POLICY
```

## Storage Rule

Audit details may include before/after values for idM-owned fields.

Audit details must not store full foreign module records or become a cross-domain identity audit store.
