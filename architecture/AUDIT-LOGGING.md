# idM Audit Logging

Status: Architecture Foundation Complete

## 1. Purpose

idM audit logging records security-relevant changes to idM-owned access identity entities.

## 2. Audit Ownership

idM owns audit records only for idM access identity activity.

idM does not own canonical IdentityAuditRecord or cross-domain audit history.

## 3. Storage Strategy

Audit records are stored in the idM-owned database table:

```text
idm_audit_log
```

## 4. Retention Strategy

Retention duration remains an accepted open item for implementation planning.

MVP recommendation:

- Keep audit records for a minimum operational period.
- Do not delete audit records silently.
- Define retention before production use.

## 5. Required Audit Fields

| Field | Type | Notes |
|---|---|---|
| auditId | UUID | idM-owned audit identifier |
| entityType | string | idM entity type |
| entityId | UUID | idM entity identifier |
| action | enum | Required event name |
| actorType | enum | USER, SERVICE_ACCOUNT, SYSTEM |
| actorId | UUID/null | idM actor identifier where known |
| timestamp | datetime | Event timestamp |
| detailsJson | JSON/text | Structured details for idM-owned fields |

## 6. Required Event Matrix

| Entity | Events |
|---|---|
| User | CREATE_USER, UPDATE_USER, DISABLE_USER, ENABLE_USER, LOCK_USER, UNLOCK_USER, ASSIGN_ROLE, REMOVE_ROLE |
| Role | CREATE_ROLE, UPDATE_ROLE, DISABLE_ROLE, ASSIGN_PERMISSION, REMOVE_PERMISSION |
| Permission | CREATE_PERMISSION, UPDATE_PERMISSION |
| ServiceAccount | CREATE_SERVICE_ACCOUNT, UPDATE_SERVICE_ACCOUNT, DISABLE_SERVICE_ACCOUNT, ENABLE_SERVICE_ACCOUNT, LOCK_SERVICE_ACCOUNT, UNLOCK_SERVICE_ACCOUNT |
| AccessPolicy | CREATE_ACCESS_POLICY, UPDATE_ACCESS_POLICY, ACTIVATE_ACCESS_POLICY, RETIRE_ACCESS_POLICY |
| TokenReference | CREATE_TOKEN_REFERENCE, REVOKE_TOKEN_REFERENCE, EXPIRE_TOKEN_REFERENCE |

Required events:

```text
CREATE_USER
UPDATE_USER
DISABLE_USER
ENABLE_USER
LOCK_USER
UNLOCK_USER
CREATE_ROLE
UPDATE_ROLE
DISABLE_ROLE
CREATE_PERMISSION
UPDATE_PERMISSION
CREATE_SERVICE_ACCOUNT
UPDATE_SERVICE_ACCOUNT
DISABLE_SERVICE_ACCOUNT
ENABLE_SERVICE_ACCOUNT
LOCK_SERVICE_ACCOUNT
UNLOCK_SERVICE_ACCOUNT
CREATE_ACCESS_POLICY
UPDATE_ACCESS_POLICY
ACTIVATE_ACCESS_POLICY
RETIRE_ACCESS_POLICY
CREATE_TOKEN_REFERENCE
REVOKE_TOKEN_REFERENCE
EXPIRE_TOKEN_REFERENCE
ASSIGN_ROLE
REMOVE_ROLE
ASSIGN_PERMISSION
REMOVE_PERMISSION
```

## 7. Actor Model

Allowed actor types:

```text
USER
SERVICE_ACCOUNT
SYSTEM
```

Actor identifiers must refer only to idM-owned User or ServiceAccount subjects where available.

## 8. Data Classification

Audit data may contain security-relevant operational details.

Audit records must not store full foreign module records, canonical identities, external identifiers, or cross-domain identity mappings.

## 9. Tamper And Integrity Assumptions

MVP design expects append-only behavior where practical.

Post-MVP may add hash chaining, export signing, or write-once storage if governance requires stronger tamper evidence.

## 10. MVP Limitations

- No final retention period yet.
- No hash-chain requirement yet.
- No centralized cross-module audit ownership.
- No canonical IdentityAuditRecord.

## 11. Post-MVP Recommendations

- Define retention period.
- Define audit export format.
- Add audit review workflow.
- Consider tamper-evidence controls after implementation risk review.
