# Table: idm_permissions

Status: MVP Ready

## Purpose

Stores idM-owned permission definitions.

## Columns

| Column | Type | Constraints |
|---|---|---|
| permissionId | CHAR(36) | Primary key |
| permissionCode | VARCHAR(160) | Required, unique |
| permissionName | VARCHAR(180) | Required |
| description | TEXT | Optional |
| status | VARCHAR(24) | Required: ACTIVE, DISABLED |
| createdAt | DATETIME | Required |
| updatedAt | DATETIME | Required |

## Indexes

- Primary key: `permissionId`
- Unique: `permissionCode`
- Index: `status`
