# RES-005: Token Management Patterns

Status: COMPLETE
Decision: ACCEPTED FOR MVP FOUNDATION

## Question

What token scope belongs in idM MVP?

## Findings

The MVP should store TokenReference metadata only.

No full OAuth/OIDC provider is required for Architecture Foundation Complete.

## OK-Core Impact

This avoids over-expanding idM into platform authentication infrastructure.

## idM Decision

Store token reference metadata, expiration, revocation and audit state only.

## Remaining Risks

Secret storage and token issuance mechanics must be hardened during implementation.

## Recommendation

Do not store plain-text token secrets. Audit create, revoke and expire operations.
