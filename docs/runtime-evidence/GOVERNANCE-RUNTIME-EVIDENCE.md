# Governance Runtime Evidence

Date: 2026-06-05
Result: PASS

## Allowed Runtime Entities

Runtime source contains services and repositories for the approved idM ownership set:

```text
User
Role
Permission
ServiceAccount
AccessPolicy
TokenReference
```

Evidence paths:

```text
src/Domain/Service/UserService.php
src/Domain/Service/RoleService.php
src/Domain/Service/PermissionService.php
src/Domain/Service/ServiceAccountService.php
src/Domain/Service/AccessPolicyService.php
src/Domain/Service/TokenReferenceService.php
src/Repository/UserRepository.php
src/Repository/RoleRepository.php
src/Repository/PermissionRepository.php
src/Repository/ServiceAccountRepository.php
src/Repository/AccessPolicyRepository.php
src/Repository/TokenReferenceRepository.php
```

## Forbidden Runtime Ownership Concepts

Checked absence in runtime source:

```text
CanonicalIdentity
IdentityRegistry
IdentityMapping
ExternalIdentifier
IdentityResolution
```

Result:

```text
No forbidden runtime ownership implementation found.
```

Scan scope:

```text
src
public
migrations
tests
```
