# Token Strategy

Status: Architecture Foundation

## Decision

idM stores TokenReference records only.

idM does not design or own a full OAuth platform in the MVP foundation.

## TokenReference Fields

| Field | Type | Notes |
|---|---|---|
| tokenReferenceId | UUID | idM-owned identifier |
| subjectType | enum | USER or SERVICE_ACCOUNT |
| subjectId | UUID | idM subject identifier |
| issuedAt | datetime | Issue timestamp |
| expiresAt | datetime | Expiration timestamp |
| revokedAt | datetime | Revocation timestamp, nullable |
| status | enum | ACTIVE, EXPIRED, REVOKED |

## Storage Principle

Token secrets should not be stored as plain text.

TokenReference stores metadata and references sufficient for lifecycle, revocation, and audit context.
