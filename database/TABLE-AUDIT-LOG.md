# Table: idm_audit_log

Status: MVP Ready

## Purpose

Stores idM access audit records.

This table is not a canonical IdentityAuditRecord store.

## Columns

| Column | Type | Constraints |
|---|---|---|
| auditId | CHAR(36) | Primary key |
| entityType | VARCHAR(64) | Required |
| entityId | CHAR(36) | Required |
| action | VARCHAR(48) | Required |
| actorType | VARCHAR(32) | Required: USER, SERVICE_ACCOUNT, SYSTEM |
| actorId | CHAR(36) | Optional idM actor identifier |
| timestamp | DATETIME | Required |
| detailsJson | JSON | Optional structured details |

## Indexes

- Primary key: `auditId`
- Index: `entityType`, `entityId`
- Index: `action`
- Index: `timestamp`

## Allowed Actions

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
ISSUE_TOKEN
REVOKE_TOKEN
```
