# Table: idm_service_accounts

Status: MVP Ready

## Purpose

Stores idM-owned non-human access subjects.

## Columns

| Column | Type | Constraints |
|---|---|---|
| serviceAccountId | CHAR(36) | Primary key |
| accountName | VARCHAR(160) | Required, unique |
| description | TEXT | Optional |
| status | VARCHAR(24) | Required: PENDING, ACTIVE, DISABLED, LOCKED |
| createdAt | DATETIME | Required |
| updatedAt | DATETIME | Required |

## Indexes

- Primary key: `serviceAccountId`
- Unique: `accountName`
- Index: `status`

## Boundary

This table stores access subjects only, not module ownership records.
