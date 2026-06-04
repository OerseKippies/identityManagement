# idM Database Boundary

Status: Architecture Foundation

## Ownership

idM may contain only identity/access data owned by idM:

```text
User
Role
Permission
ServiceAccount
AccessPolicy
TokenReference
RolePermission links
UserRole links
ServiceAccountRole links
```

## Allowed References

```text
foreignModuleObjectId
externalReferenceId
snapshotForAudit
cachedReadModel
```

## Forbidden

```text
Foreign module master tables
Foreign module business logic tables
Shared mutable tables
Direct writes to another module database
Canonical business-object identity tables
Cross-domain identity mapping tables
```

## Principle

The idM database is not a hidden registry for the ecosystem.

It is a module-owned access identity store.
