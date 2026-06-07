# IDM-CURRENT-LOGIN-METHOD

Date: 2026-06-07T22:01:00Z  
Repository: OerseKippies/identityManagement  
Authority: OerseKippies/OK-Core/START-HERE.md  
Target: https://idm.oerse-kippies.nl  
Classification: Runtime Evidence — Current Login Enablement (CCM)

## Result

```text
SUCCESS
```

Login is operational for the single production user via contract-driven actor-context resolution. No password or OAuth login exists; copM presents a user picker and resolves identity through commL.

---

## Existing Users (Production)

Captured: `GET /v1/identity/users` with `X-Source-Module: communicationLayer` (2026-06-07T22:00 UTC).

| userId | username | displayName | email | status | createdAt |
|---|---|---|---|---|---|
| `bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb` | `copm.probe` | copM Probe User | `copm.probe@example.test` | **ACTIVE** | 2026-06-06 14:23:17 |

**Total users:** 1  
**ACTIVE users:** 1 (`copM Probe User`)  
**PENDING / DISABLED / LOCKED:** 0

Source seed: `migrations/002_copm_probe_seed.sql`

---

## Login Mechanism (Current)

idM does **not** expose username/password, OAuth, or OIDC login endpoints.

Phase 7 MVP authentication is contract-driven:

| Step | Contract / endpoint | Owner |
|---|---|---|
| List selectable users | `idM.users.list.v1` → `GET /v1/identity/users` | idM (via commL) |
| Resolve actor (login) | `idM.actorContext.resolve.v1` → `POST /v1/identity/actor-context` | idM (via commL) |
| Presentation session | PHP `$_SESSION['copm_presentation_session']` | copM only |

**End-user flow today (copM):**

```text
Operator opens copM workspace
  → copM calls commL (idM.users.list.v1)
  → User picker shows ACTIVE users
  → Operator selects user, submits "Login via commL"
  → copM calls commL (idM.actorContext.resolve.v1) with user UUID as subjectHint
  → idM returns actor context
  → copM stores presentation session (authenticated state)
```

Reference: `contracts/COPM-IDM-AUTH-CONTRACT.md`, `runtime-evidence/IDM-AUTHENTICATION-FLOW-EVIDENCE.md`

Implementation anchor: `src/Domain/Service/ActorContextService.php`

---

## Allowed Login Identifiers

Verification performed: `POST /v1/identity/actor-context` (production, 2026-06-07T22:00 UTC).

| Identifier | Accepted as login input? | Evidence |
|---|---|---|
| **subjectHint** (user UUID) | **YES** — sole accepted login identifier | HTTP 200; actor context returned |
| **username** | **NO** | HTTP 400 `VALIDATION_ERROR`: subjectHint must be a valid user UUID |
| **email** | **NO** | HTTP 400 `VALIDATION_ERROR`: subjectHint must be a valid user UUID |
| **actorId** (request field) | **NO** — output only, not read as input | HTTP 400 when actorId sent without subjectHint |
| **subjectHint** missing | **NO** | HTTP 400 `VALIDATION_ERROR` |

**Note:** `actorId` appears in the **response** after successful resolution; it is not an accepted request field for login.

---

## Required Fields

### `POST /v1/identity/actor-context`

**Headers (commL-mediated):**

```text
Content-Type: application/json
X-Source-Module: communicationLayer
X-Correlation-Id: <uuid-v4>
```

**Body:**

| Field | Required | Values (USER login) |
|---|---|---|
| `credentialType` | Optional (defaults to `USER`) | `USER` |
| `subjectHint` | **Required** | Valid UUID v4 matching an existing `idm_users.userId` |
| `presentationSessionId` | Optional | copM-owned session UUID (not validated by idM) |

Direct API access (non-commL) additionally requires `X-Api-Key` when `api.require_api_key` is enabled.

### copM UI login (operator path)

| Field | Required | Source |
|---|---|---|
| `copm_action` | Yes | `idm_login` |
| `user_id` | Yes | Selected user UUID from users list |

---

## Successful Login Example

### idM actor-context (direct contract surface)

```http
POST /v1/identity/actor-context HTTP/1.1
Host: idm.oerse-kippies.nl
Content-Type: application/json
X-Source-Module: communicationLayer
X-Correlation-Id: 132f8d98-8df8-464a-9b54-0c87f3d6c6c7
```

```json
{
  "credentialType": "USER",
  "subjectHint": "bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb"
}
```

**Response (HTTP 200):**

```json
{
  "actorType": "USER",
  "actorId": "bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb",
  "displayName": "copM Probe User",
  "status": "ACTIVE",
  "roles": [],
  "permissions": [],
  "tokenReferenceId": null,
  "correlationId": "132f8d98-8df8-464a-9b54-0c87f3d6c6c7"
}
```

### copM end-to-end (operator)

```http
POST https://copilot.oerse-kippies.nl/
Content-Type: application/x-www-form-urlencoded

copm_action=idm_login&user_id=bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb
```

**Outcome:** Presentation session created; UI shows `copM Probe User`, authenticated badge.

---

## Failed Login Examples

### Username as subjectHint

```json
{
  "credentialType": "USER",
  "subjectHint": "copm.probe"
}
```

**Response (HTTP 400):**

```json
{
  "error": {
    "errorCode": "VALIDATION_ERROR",
    "errorMessage": "subjectHint must be a valid user UUID",
    "correlationId": "e07d9692-2f30-4328-b15e-1c4bb0b56d13",
    "timestamp": "2026-06-07T22:00:37Z"
  }
}
```

### Unknown user UUID

```json
{
  "credentialType": "USER",
  "subjectHint": "aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa"
}
```

**Response (HTTP 404):**

```json
{
  "error": {
    "errorCode": "NOT_FOUND",
    "errorMessage": "user not found",
    "correlationId": "4288f2ba-ec54-483d-a47c-c033d4033984",
    "timestamp": "2026-06-07T22:00:47Z"
  }
}
```

---

## Current Limitations

| Limitation | Impact |
|---|---|
| No password / credential verification | Login is identity selection, not proof-of-possession |
| Single probe user in production | Only `copM Probe User` can authenticate today |
| subjectHint must be user UUID | Username and email cannot be used to log in |
| No status gate on resolve | ACTIVE, PENDING, DISABLED, and LOCKED users all resolve; status is returned but not enforced |
| TokenReference not wired to login | `tokenReferenceId` always `null` for USER credential type |
| copM login UI below the fold | Login panel exists but is not in header chrome (discoverability gap) |
| No device-bound authentication | See `roadmap/AUTH-BACKLOG-001-DEVICE-AUTHENTICATION.md` |
| Direct idM login UI absent | Operators must use copM (or API consumer) with commL mediation |

---

## GitHub Evidence

| Field | Value |
|---|---|
| File | `runtime/evidence/IDM-CURRENT-LOGIN-METHOD.md` |
| Commit | `0c5357d` |
| Repository | https://github.com/OerseKippies/identityManagement |

## Related Evidence

- `runtime-evidence/IDM-AUTHENTICATION-FLOW-AUDIT.md`
- `runtime-evidence/IDM-AUTHENTICATION-FLOW-EVIDENCE.md`
- `contracts/COPM-IDM-AUTH-CONTRACT.md`
- `runtime/evidence/PHASE9-IDM-READINESS.md`
