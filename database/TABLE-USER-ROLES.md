# Table: idm_user_roles

Status: MVP Ready

## Purpose

Stores idM-owned role assignments for users.

## Columns

| Column | Type | Constraints |
|---|---|---|
| userRoleId | CHAR(36) | Primary key |
| userId | CHAR(36) | Required, references idM users.userId |
| roleId | CHAR(36) | Required, references idM roles.roleId |
| assignedAt | DATETIME | Required |
| assignedByType | VARCHAR(24) | Required: USER, SERVICE_ACCOUNT, SYSTEM |
| assignedById | CHAR(36) | Optional idM actor identifier |

## Indexes

- Primary key: `userRoleId`
- Unique: `userId`, `roleId`
- Index: `userId`
- Index: `roleId`

## Boundary

All references are internal idM UUIDs.
