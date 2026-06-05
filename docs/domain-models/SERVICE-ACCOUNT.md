# ServiceAccount

Status: MVP Runtime

Non-human access subject owned by idM.

## Fields

| Field | Type | Notes |
|---|---|---|
| serviceAccountId | UUID | Primary key |
| accountName | string | Unique |
| description | string | Optional |
| status | enum | PENDING, ACTIVE, DISABLED, LOCKED |
| createdAt | datetime | UTC |
| updatedAt | datetime | UTC |

## Runtime

`src/Domain/Service/ServiceAccountService.php`, table `idm_service_accounts`
