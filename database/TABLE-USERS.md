# Table: idm_users

Status: MVP Ready

## Purpose

Stores idM-owned human access subjects.

## Columns

| Column | Type | Constraints |
|---|---|---|
| userId | CHAR(36) | Primary key |
| username | VARCHAR(120) | Required, unique |
| displayName | VARCHAR(180) | Required |
| email | VARCHAR(255) | Required, unique |
| status | VARCHAR(24) | Required: PENDING, ACTIVE, DISABLED, LOCKED |
| createdAt | DATETIME | Required |
| updatedAt | DATETIME | Required |

## Indexes

- Primary key: `userId`
- Unique: `username`
- Unique: `email`
- Index: `status`

## Boundary

This table does not store ContactId, CustomerId, or other foreign module identifiers.
