# Table: idm_role_permissions

Status: MVP Ready

## Purpose

Stores idM-owned permission assignments for roles.

## Columns

| Column | Type | Constraints |
|---|---|---|
| rolePermissionId | CHAR(36) | Primary key |
| roleId | CHAR(36) | Required, references idM roles.roleId |
| permissionId | CHAR(36) | Required, references idM permissions.permissionId |
| assignedAt | DATETIME | Required |
| assignedByType | VARCHAR(24) | Required: USER, SERVICE_ACCOUNT, SYSTEM |
| assignedById | CHAR(36) | Optional idM actor identifier |

## Indexes

- Primary key: `rolePermissionId`
- Unique: `roleId`, `permissionId`
- Index: `roleId`
- Index: `permissionId`

## Boundary

All references are internal idM UUIDs.
