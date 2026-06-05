# Role

Status: MVP Runtime

RBAC role owned by idM.

## Fields

| Field | Type | Notes |
|---|---|---|
| roleId | UUID | Primary key |
| roleCode | string | Unique |
| roleName | string | Required |
| description | string | Optional |
| status | enum | ACTIVE, DISABLED |
| createdAt | datetime | UTC |
| updatedAt | datetime | UTC |

## Runtime

`src/Domain/Service/RoleService.php`, table `idm_roles`
