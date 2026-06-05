# Permission

Status: MVP Runtime

RBAC permission owned by idM.

## Fields

| Field | Type | Notes |
|---|---|---|
| permissionId | UUID | Primary key |
| permissionCode | string | Unique |
| permissionName | string | Required |
| description | string | Optional |
| status | enum | ACTIVE, DISABLED |
| createdAt | datetime | UTC |
| updatedAt | datetime | UTC |

## Runtime

`src/Domain/Service/PermissionService.php`, table `idm_permissions`
