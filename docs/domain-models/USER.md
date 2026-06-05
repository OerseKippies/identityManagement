# User

Status: MVP Runtime
Module: identityManagement (idM)

Human access subject owned by idM.

## Fields

| Field | Type | Notes |
|---|---|---|
| userId | UUID | Primary key |
| username | string | Unique |
| displayName | string | Required |
| email | string | Unique |
| status | enum | PENDING, ACTIVE, DISABLED, LOCKED |
| createdAt | datetime | UTC |
| updatedAt | datetime | UTC |

## Runtime

`src/Domain/Service/UserService.php`, table `idm_users`
