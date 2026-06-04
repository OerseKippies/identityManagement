# idM MariaDB Schema

Status: MVP Ready

## Schema Ownership

The idM schema contains only idM access identity data.

No table stores foreign module references or canonical business-object identity.

## Table Summary

| Table | Purpose |
|---|---|
| idm_users | Human access subjects |
| idm_roles | Access roles |
| idm_permissions | Access permissions |
| idm_user_roles | User to role assignments |
| idm_role_permissions | Role to permission assignments |
| idm_service_accounts | Non-human access subjects |
| idm_service_account_roles | Service account to role assignments |
| idm_access_policies | Access policy definitions |
| idm_token_references | Token metadata and lifecycle references |
| idm_audit_log | idM access audit records |
| idm_schema_migrations | Applied database migrations |

## Key Strategy

All primary keys are UUID values stored as `CHAR(36)` for MVP portability on MariaDB 10.6.

## Timestamp Strategy

Use UTC timestamps for `createdAt`, `updatedAt`, `issuedAt`, `expiresAt`, `revokedAt`, and audit `timestamp`.
