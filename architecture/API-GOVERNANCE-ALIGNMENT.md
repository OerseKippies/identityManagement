# idM API Governance Alignment

Status: Architecture Foundation
API status: DRAFT_IN_MODULE
Authority: OK-Core API-GOVERNANCE.md

## Required API Design Notes

1. Purpose: access identity and authorization administration.
2. Domain owner: identityManagement (idM).
3. Core entities: User, Role, Permission, ServiceAccount, AccessPolicy, TokenReference.
4. Use cases: create/update/disable users, manage roles and permissions, manage service accounts, define access policies, reference tokens.
5. Endpoints: drafted in `docs/api/idm-api-draft.yaml`.
6. Request models: drafted in the OpenAPI file.
7. Response models: drafted in the OpenAPI file.
8. Status values: ACTIVE, DISABLED, LOCKED, PENDING, DRAFT, EXPIRED, REVOKED.
9. Error cases: validation, conflict, not found, forbidden, boundary violation.
10. Events emitted: draft idM events only.
11. Events consumed: none required for the MVP foundation.
12. External dependencies: communicationLayer (commL), OK-Core governance.
13. Out-of-scope responsibilities: canonical identity, identity mapping, identity resolution, business-object identity.

## Canonical Contract Rule

The API draft starts inside idM.

The accepted canonical API belongs in OK-Core after review.
