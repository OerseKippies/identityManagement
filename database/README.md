# idM Database Design

Status: MVP Ready
Database: MariaDB 10.6

## Purpose

This directory documents the idM-owned MariaDB schema design.

idM stores only access identity data owned by idM.

## Tables

```text
idm_users
idm_roles
idm_permissions
idm_user_roles
idm_role_permissions
idm_service_accounts
idm_service_account_roles
idm_access_policies
idm_token_references
idm_audit_log
idm_schema_migrations
```

## Rules

- Use UUID primary keys.
- Do not store foreign module references.
- Do not store canonical business-object identity.
- Do not share mutable tables with other modules.
- Do not read or write foreign module databases.
