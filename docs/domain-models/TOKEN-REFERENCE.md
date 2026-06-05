# TokenReference

Status: MVP Runtime

Token metadata and lifecycle reference. Plain token secrets are not stored.

## Fields

| Field | Type | Notes |
|---|---|---|
| tokenReferenceId | UUID | Primary key |
| subjectType | enum | USER, SERVICE_ACCOUNT |
| subjectId | UUID | idM subject identifier |
| issuedAt | datetime | UTC |
| expiresAt | datetime | UTC |
| revokedAt | datetime | Optional UTC |
| status | enum | ACTIVE, REVOKED, EXPIRED |

## Runtime

`src/Domain/Service/TokenReferenceService.php`, table `idm_token_references`
