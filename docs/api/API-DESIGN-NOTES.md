# idM API Design Notes

Status: DRAFT_IN_MODULE
Authority: OK-Core API-GOVERNANCE.md

## Purpose

The idM API draft supports access identity administration for users, roles, permissions, service accounts, access policies, and token references.

## Domain Owner

identityManagement (idM)

## Core Entities

```text
User
Role
Permission
ServiceAccount
AccessPolicy
TokenReference
```

## Use Cases

- Create, update, disable, lock, and read users.
- Create and manage roles.
- Create and manage permissions.
- Assign and remove roles for users.
- Create and manage service accounts.
- Create and manage access policies.
- Store and revoke token references.

## Status Values

```text
ACTIVE
DISABLED
LOCKED
PENDING
DRAFT
EXPIRED
REVOKED
```

## Error Cases

```text
VALIDATION_ERROR
NOT_FOUND
CONFLICT
FORBIDDEN
BOUNDARY_VIOLATION
INTERNAL_ERROR
```

## Events Emitted

Draft only:

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

## Events Consumed

None required for the MVP foundation.

## External Dependencies

```text
communicationLayer (commL)
OK-Core API governance
```

## Out Of Scope

```text
CanonicalIdentity
IdentityRegistry
IdentityMapping
ExternalIdentifier
IdentityResolution
IdentityLifecycle
IdentityAuditRecord
Business-object identity ownership
Full OAuth platform
```
