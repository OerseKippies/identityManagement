# idM Communication Contracts

Status: Draft
Routing: communicationLayer (commL)

Runtime API implements local idM administration endpoints. Cross-module command routing remains commL-mediated per OK-Core boundary.

## Command Drafts

| Contract | Runtime Endpoint |
|---|---|
| CreateUser | POST /v1/users |
| UpdateUser | PATCH /v1/users/{userId} |
| DisableUser | POST /v1/users/{userId}/disable |
| EnableUser | POST /v1/users/{userId}/enable |
| CreateRole | POST /v1/roles |
| AssignRoleToUser | POST /v1/users/{userId}/roles/{roleId} |
| RemoveRoleFromUser | DELETE /v1/users/{userId}/roles/{roleId} |
| CreatePermission | POST /v1/permissions |
| AttachPermissionToRole | POST /v1/roles/{roleId}/permissions/{permissionId} |
| RemovePermissionFromRole | DELETE /v1/roles/{roleId}/permissions/{permissionId} |
| CreateServiceAccount | POST /v1/service-accounts |
| CreateAccessPolicy | POST /v1/access-policies |
| RevokeTokenReference | POST /v1/token-references/{tokenReferenceId}/revoke |

Architecture reference: `architecture/COMMUNICATION-CONTRACTS.md`
