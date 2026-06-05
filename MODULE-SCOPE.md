# identityManagement Module Scope

Status: MVP Runtime Complete

## Owned

idM owns only:

- User
- Role
- Permission
- ServiceAccount
- AccessPolicy
- TokenReference

## Not Owned

idM does not own:

- canonical identity concepts
- business-object identity concepts
- foreign module data
- foreign module workflows
- cross-domain identity mapping
- canonical identifiers for animals, products, customers, contacts, orders, publications, advertisements, inventory items, or hatch runs

Explicitly excluded runtime/domain names:

```text
Identity
IdentityReference
IdentityCredentialReference
ServiceAccountReference
RoleReference
PermissionReference
CanonicalIdentity
IdentityRegistry
IdentityMapping
ExternalIdentifier
IdentityResolution
IdentityLifecycle
```

## Boundary Rule

idM may not become a hidden owner of foreign data through local copies, read models, audit payloads, or access policy definitions.
