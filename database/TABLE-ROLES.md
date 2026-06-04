# Table: idm_roles

Status: MVP Ready

## Purpose

Stores idM-owned access roles.

## Columns

| Column | Type | Constraints |
|---|---|---|
| roleId | CHAR(36) | Primary key |
| roleCode | VARCHAR(120) | Required, unique |
| roleName | VARCHAR(180) | Required |
| description | TEXT | Optional |
| status | VARCHAR(24) | Required: ACTIVE, DISABLED |
| createdAt | DATETIME | Required |
| updatedAt | DATETIME | Required |

## Indexes

- Primary key: `roleId`
- Unique: `roleCode`
- Index: `status`
