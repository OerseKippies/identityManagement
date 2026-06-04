# Table: idm_token_references

Status: MVP Ready

## Purpose

Stores token metadata and lifecycle references.

idM does not store plain token secrets and does not implement a full OAuth platform in the MVP.

## Columns

| Column | Type | Constraints |
|---|---|---|
| tokenReferenceId | CHAR(36) | Primary key |
| subjectType | VARCHAR(32) | Required: USER, SERVICE_ACCOUNT |
| subjectId | CHAR(36) | Required idM subject identifier |
| issuedAt | DATETIME | Required |
| expiresAt | DATETIME | Required |
| revokedAt | DATETIME | Optional |
| status | VARCHAR(24) | Required: ACTIVE, REVOKED, EXPIRED |

## Indexes

- Primary key: `tokenReferenceId`
- Index: `subjectType`, `subjectId`
- Index: `status`
- Index: `expiresAt`

## Boundary

Subject identifiers are idM-owned User or ServiceAccount UUIDs.
