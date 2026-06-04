# idM Communication Contracts

Status: Draft
Routing: communicationLayer (commL)

All contracts are drafts only and must be routed through communicationLayer (commL).

## Command Drafts

| Contract | Purpose |
|---|---|
| CreateUser | Create an idM user |
| UpdateUser | Update idM user profile fields |
| DisableUser | Disable user access |
| CreateRole | Create an idM role |
| AssignRoleToUser | Assign role to user |
| RemoveRoleFromUser | Remove role from user |
| CreatePermission | Create an idM permission |
| AttachPermissionToRole | Attach permission to role |
| CreateServiceAccount | Create a non-human access subject |
| CreateAccessPolicy | Create an access policy |

## Event Drafts

Potential idM events for future OK-Core review:

```text
idm.user.created
idm.user.updated
idm.user.disabled
idm.role.created
idm.role.assignedToUser
idm.role.removedFromUser
idm.permission.created
idm.permission.attachedToRole
idm.serviceAccount.created
idm.accessPolicy.created
idm.tokenReference.revoked
```

## Boundary

These drafts do not create canonical ecosystem API contracts.

Canonical contracts belong in OK-Core after review.
