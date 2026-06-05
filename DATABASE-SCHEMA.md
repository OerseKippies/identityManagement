# idM Database Schema

Status: MVP Runtime
Engine: MariaDB 10.6

## Ownership

The idM schema stores only idM-owned access identity data.

## Tables

| Table | Purpose |
|---|---|
| idm_users | Human access subjects (User) |
| idm_roles | RBAC roles (Role) |
| idm_permissions | RBAC permissions (Permission) |
| idm_user_roles | User role assignments |
| idm_role_permissions | Role permission assignments |
| idm_service_accounts | Non-human access subjects (ServiceAccount) |
| idm_service_account_roles | Service account role assignments |
| idm_access_policies | Access policies (AccessPolicy) |
| idm_token_references | Token metadata references (TokenReference) |
| idm_audit_log | Append-only audit records |
| idm_schema_migrations | Applied migration registry |

## Migration

```text
migrations/001_initial_schema.sql
scripts/migrate.php
```

## Design Reference

Table-level documentation: `database/TABLE-*.md` and `database/SCHEMA.md`
