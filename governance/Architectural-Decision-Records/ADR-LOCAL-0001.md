# ADR-LOCAL-0001

Status: Accepted
Date: 2026-06-04

## Decision

OK-Core governance selected Option A.

identityManagement (idM) is an Access Identity module.

Owned:
- User
- Role
- Permission
- ServiceAccount
- AccessPolicy
- TokenReference

Not owned:
- CanonicalIdentity
- IdentityRegistry
- IdentityMapping
- ExternalIdentifier
- IdentityResolution
- IdentityLifecycle
- IdentityAuditRecord
- Any business object identifier

All communication must pass through communicationLayer (commL).