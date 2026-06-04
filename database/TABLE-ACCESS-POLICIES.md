# Table: idm_access_policies

Status: MVP Ready

## Purpose

Stores idM-owned access policy definitions.

## Columns

| Column | Type | Constraints |
|---|---|---|
| policyId | CHAR(36) | Primary key |
| policyCode | VARCHAR(160) | Required, unique |
| policyName | VARCHAR(180) | Required |
| description | TEXT | Optional |
| status | VARCHAR(24) | Required: DRAFT, ACTIVE, RETIRED |
| createdAt | DATETIME | Required |
| updatedAt | DATETIME | Required |

## Indexes

- Primary key: `policyId`
- Unique: `policyCode`
- Index: `status`

## Boundary

Access policies may govern idM access behavior only.
