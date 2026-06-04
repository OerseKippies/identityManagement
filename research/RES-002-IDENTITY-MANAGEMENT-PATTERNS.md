# RES-002: Identity Management Patterns

Status: COMPLETE
Decision: ACCEPTED FOR MVP FOUNDATION

## Question

Which identity-management scope is appropriate for idM MVP?

## Findings

The accepted OK-Core boundary makes idM an Access Identity module, not a canonical identity registry.

## OK-Core Impact

CanonicalIdentity, IdentityRegistry, IdentityMapping, ExternalIdentifier, IdentityResolution, IdentityLifecycle and IdentityAuditRecord remain out of scope.

## idM Decision

Model only User, Role, Permission, ServiceAccount, AccessPolicy and TokenReference.

## Remaining Risks

Future ecosystem identity registry needs may arise, but must be handled by OK-Core governance.

## Recommendation

Keep idM narrow and avoid cross-domain identity mapping.
