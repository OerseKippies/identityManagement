# AccessPolicy

Status: MVP Runtime

idM-owned access policy definition.

## Fields

| Field | Type | Notes |
|---|---|---|
| policyId | UUID | Primary key |
| policyCode | string | Unique |
| policyName | string | Required |
| description | string | Optional |
| status | enum | DRAFT, ACTIVE, RETIRED |
| createdAt | datetime | UTC |
| updatedAt | datetime | UTC |

## Runtime

`src/Domain/Service/AccessPolicyService.php`, table `idm_access_policies`
