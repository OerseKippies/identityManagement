# Table: idm_service_account_roles

Status: MVP Implementation Foundation Complete

## Purpose

Stores idM-owned role assignments for service accounts.

No endpoint is exposed for this assignment in the first MVP route set, but the table is part of the required idM schema foundation.

## Columns

| Column | Type | Constraints |
|---|---|---|
| serviceAccountRoleId | CHAR(36) | Primary key |
| serviceAccountId | CHAR(36) | Required, references idM service_accounts.serviceAccountId |
| roleId | CHAR(36) | Required, references idM roles.roleId |
| assignedAt | DATETIME | Required |
| assignedByType | VARCHAR(24) | Required: USER, SERVICE_ACCOUNT, SYSTEM |
| assignedById | CHAR(36) | Optional idM actor identifier |

## Indexes

- Primary key: `serviceAccountRoleId`
- Unique: `serviceAccountId`, `roleId`
- Index: `serviceAccountId`
- Index: `roleId`

## Boundary

All references are internal idM UUIDs.
