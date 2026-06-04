# ADR-LOCAL-0001: Access Identity Boundary

Status: Accepted
Date: 2026-06-04
Authority: OK-Core Option A

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

## Consequences

- idM documentation and implementation must remain limited to access identity.
- idM may store references to external subjects only when needed for access control and audit context.
- idM must not define CanonicalIdentity, IdentityRegistry, IdentityMapping, ExternalIdentifier, IdentityResolution, IdentityLifecycle, or IdentityAuditRecord.
- idM API drafts remain DRAFT_IN_MODULE until reviewed and canonized by OK-Core.
