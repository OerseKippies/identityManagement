# COPM ↔ idM Authentication Contract (via commL)

Status: APPROVED (Phase 7 Integration)
Date: 2026-06-06
Routing: communicationLayer (commL) — mandatory

## Parties

| Party | Code | Role |
|---|---|---|
| Consumer | coPilotManagement (copM) | UI workspace; initiates authentication |
| Mediator | communicationLayer (commL) | Contract enforcement, routing, consumer authorization |
| Provider | identityManagement (idM) | Access identity and actor context authority |

## Flow

```text
1. copM collects operator credentials or session proof (presentation layer only)
2. copM calls commL with contractId idM.actorContext.resolve.v1
3. commL validates sourceModule = coPilotManagement in allowedConsumers
4. commL forwards request to idM with correlationId
5. idM resolves actor context from idM-owned User / ServiceAccount / TokenReference
6. idM returns actor context; commL returns response to copM
7. copM stores presentation session state only (no identity ownership)
```

## commL Contract

| Field | Value |
|---|---|
| contractId | `idM.actorContext.resolve.v1` |
| method | POST |
| path | `/api/v1/identity/actor-context` |
| mutation | false |
| requiresCorrelationId | true |
| ownerModule | identityManagement |
| allowedConsumers (Phase 7) | `coPilotManagement` (+ existing MVP consumers as registered) |

Registry: `OerseKippies/communicationLayer/contracts/contracts.json`

## Request (copM → commL → idM)

Headers (commL + idM):

```text
X-Correlation-Id: <uuid-v4>
X-Source-Module: coPilotManagement
Content-Type: application/json
```

Body (draft):

```json
{
  "credentialType": "PASSWORD | TOKEN_REFERENCE | SERVICE_ACCOUNT",
  "subjectHint": "<optional idM subject UUID>",
  "presentationSessionId": "<copM presentation session UUID>"
}
```

`presentationSessionId` is owned by copM UI state only.

## Response (idM → commL → copM)

Allowed fields:

```json
{
  "actorType": "USER | SERVICE_ACCOUNT | SYSTEM",
  "actorId": "<idM UUID>",
  "displayName": "<string>",
  "status": "ACTIVE | DISABLED | LOCKED | PENDING",
  "roles": ["<idM roleCode>"],
  "permissions": ["<idM permissionCode>"],
  "tokenReferenceId": "<idM UUID or null>",
  "correlationId": "<uuid-v4>"
}
```

## Allowed Claims

```text
actorType, actorId, displayName, status
roles, permissions (idM RBAC)
tokenReferenceId (metadata only)
correlationId
```

## Forbidden Claims

```text
CanonicalIdentity, IdentityRegistry, IdentityMapping
ExternalIdentifier, IdentityResolution, IdentityLifecycle
Identity, IdentityReference
CustomerId, ContactId, or any foreign business-object identity as idM ownership
Plain token secrets, password hashes, reversible credentials
Cross-domain identity mapping
```

## Ownership Rules

- idM owns access identity resolution and RBAC truth.
- commL owns mediation and consumer authorization.
- copM owns UI presentation state only.
- copM must not persist idM databases or become a hidden identity registry.

## Error Model

Standard idM error envelope with `correlationId`:

```text
VALIDATION_ERROR, UNAUTHORIZED, FORBIDDEN, NOT_FOUND, INTERNAL_ERROR
```

commL may add `CONSUMER_NOT_ALLOWED` before forwarding.

## Runtime Evidence (Phase 7 closure)

```text
docs/runtime-evidence/ — copM + commL + idM integration smoke
communicationLayer/runtime/evidence/ — consumer authorization PASS
identityManagement/docs/runtime-evidence/ — actor-context audit correlation
```

## References

- `reviews/REVIEW-PHASE7-IDM-AUTH-INTEGRATION.md`
- `OerseKippies/OK-Core/implementation/PHASE7-UX-FOUNDATION.md`
- `MODULE-SCOPE.md`
- `SECURITY-MODEL.md`
