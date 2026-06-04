# idM Proposed Architecture

Status: Architecture Foundation

## Summary

identityManagement (idM) provides access identity capabilities for the OK ecosystem.

The MVP architecture is a PHP 8.3 and MariaDB module that owns users, roles, permissions, service accounts, access policies, and token references.

## Components

```text
idM API
idM application logic
idM MariaDB schema
idM scheduled maintenance jobs
idM API draft documentation
```

## Data Model

```text
User
UserRole
Role
RolePermission
Permission
ServiceAccount
ServiceAccountRole
AccessPolicy
TokenReference
```

## Communication

All cross-module communication flows through communicationLayer (commL).

```text
Module -> commL -> idM
idM -> commL -> Module
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
Full OAuth platform
Business-object identity ownership
```
