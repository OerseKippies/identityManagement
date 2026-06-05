# ADR-LOCAL-0001: Access Identity Boundary

Status: Accepted
Authority: OK-Core Option A

identityManagement (idM) is an Access Identity module.

## Owned

```text
User
Role
Permission
ServiceAccount
AccessPolicy
TokenReference
```

## Explicitly Not Owned

```text
CanonicalIdentity
IdentityRegistry
IdentityMapping
ExternalIdentifier
IdentityResolution
IdentityLifecycle
IdentityAuditRecord
```

All cross-module communication routes through communicationLayer (commL).

Full record: `governance/Architectural-Decision-Records/ADR-LOCAL-0001.md`
