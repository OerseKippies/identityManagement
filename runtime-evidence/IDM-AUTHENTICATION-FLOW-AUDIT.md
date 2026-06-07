# idM Authentication Flow Audit

**Authority:** Operational MVP Complete — APR-2026-06-07-030  
**Date:** 2026-06-07  
**Auditor:** OK-Core runtime validation  
**Repositories:** OerseKippies/identityManagement, coPilotManagement, communicationLayer  
**Production:** Versio (`copilot.oerse-kippies.nl`, `comml.oerse-kippies.nl`, `idm.oerse-kippies.nl`)

## Executive decision

| Field | Result |
|---|---|
| **Decision** | **B** — Authentication implemented but inaccessible from primary UI |
| Login Endpoint | PASS |
| Login UI | PASS |
| Session Creation | PASS |
| Authentication Flow | PASS |

## Observed production state (operator view)

On initial load, copM displays:

- `unauthenticated` auth badge (header)
- `Guest` / `Not signed in via idM` (header and sidebar)
- `No session` in status widgets
- **No login button in header or sidebar** — only read-only status labels

This matches the reported production observation. Login is not missing from the deployment; it is **not surfaced in the primary chrome**.

## MVP authentication model

idM does **not** expose a password/OAuth `/login` endpoint. Phase 7 MVP auth is contract-driven:

| Step | Contract / endpoint | Owner |
|---|---|---|
| List selectable users | `idM.users.list.v1` → `GET /api/v1/identity/users` | idM via commL |
| Resolve actor (login) | `idM.actorContext.resolve.v1` → `POST /api/v1/identity/actor-context` | idM via commL |
| Presentation session | PHP `$_SESSION['copm_presentation_session']` | copM only |

Reference: `contracts/COPM-IDM-AUTH-CONTRACT.md`

TokenReference APIs (`POST/GET /v1/token-references`) exist in idM but are **not** wired into the copM MVP login UI. USER credential resolution returns `tokenReferenceId: null` by design in `ActorContextService`.

## Investigation checklist

### 1. Login endpoint exists — PASS

idM registers the MVP login surface:

- `GET /v1/identity/users` — user picker data
- `POST /v1/identity/actor-context` — actor context resolution (login)

Source: `src/Application.php` lines 148–153.

Production: `GET https://idm.oerse-kippies.nl/api/v1/identity/users` (with commL mediation headers) returns probe user `copM Probe User`.

### 2. Login route registered in commL — PASS

`communicationLayer/contracts/contracts.json`:

- `idM.users.list.v1` — `coPilotManagement` in `allowedConsumers`
- `idM.actorContext.resolve.v1` — `coPilotManagement` in `allowedConsumers`

`communicationLayer/routes/routes.json`: `identityManagement` → `https://idm.oerse-kippies.nl`, `status: active`.

commL audit log (`storage/audit/audit-2026-06-07.jsonl`) shows successful `idM.users.list.v1` forwards from `coPilotManagement` with HTTP 200.

### 3. Login UI exists in copM — PASS (discoverability FAIL)

Login UI is implemented in `coPilotManagement/templates/partials/idm-auth-panel.php`:

- User `<select>` populated from `idM.users.list.v1`
- Submit button: **Login via commL** (`copm_action=idm_login`)
- Logout: **Logout (clear presentation session)** (`copm_action=idm_logout`)

Widget placement: `idm-auth` is **display order 3** in default workspace layout (after Unified Dashboard, commL Visibility, User Profile). Login requires scrolling below the fold.

Header (`auth-status.php`, `current-user.php`) shows status only when unauthenticated — **no sign-in affordance**.

Production HTML (2026-06-07): `Login via commL` button present and enabled with `copM Probe User` option.

### 4. Session creation works — PASS

`coPilotManagement/src-php/Auth/SessionStore.php` writes `copm_presentation_session` with `presentationSessionId`, `actorContext`, `correlationId`, `authenticatedAt`.

`IdmService::loginWithUser()` calls `resolveActorContext()` then `SessionStore::write()`.

Production POST:

```http
POST https://copilot.oerse-kippies.nl/
copm_action=idm_login&user_id=bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb
```

Result: `workspace-shell__auth--authenticated`, display name `copM Probe User`, logout controls visible in header.

### 5. Token issuance works — PASS (MVP scope)

MVP login does not issue bearer/JWT tokens. Issuance in scope:

- **Presentation session UUID** — created by `SessionStore::createPresentationSessionId()` on login
- **TokenReference CRUD** — available on idM admin API (`/v1/token-references`) but not consumed by copM login

For operator authentication, presentation session + actor context is the intended MVP token surface.

### 6. Token validation works — PASS (MVP scope)

Validation is copM-side:

- `SessionStore::read()` on each request
- `IdmService::fetchAuthVisibility()` derives `authState` from session presence + idM reachability
- Actor context fields (`actorId`, `displayName`, `roles`, `permissions`) stored at login and read from session

No separate token introspection endpoint is required for MVP USER picker login.

### 7. Logout flow exists — PASS

- `IdmService::logoutPresentation()` → `SessionStore::clear()`
- UI: idm-auth panel + header `current-user.php` logout form when authenticated

Production POST `copm_action=idm_logout` restores `workspace-shell__auth--unauthenticated` and `Guest`.

### 8. State transition UNAUTHENTICATED → AUTHENTICATED — PASS

Verified on production via cookie-backed POST login and logout sequence (see `IDM-AUTHENTICATION-FLOW-EVIDENCE.md`).

## Decision matrix

| Code | Criteria | Verdict |
|---|---|---|
| A | Implemented and reachable from primary UI | No — header/sidebar lack login |
| **B** | **Implemented but inaccessible from primary UI** | **Yes — login buried in Identity widget** |
| C | Not implemented | No — endpoints, commL routes, copM handlers, and UI exist |
| D | Disabled by configuration | No — idM active, users listed, login button enabled |

## Root cause of operator report

Operators land on **Unified Dashboard** (default `defaultLandingPage`) and see auth status in the shell without a call-to-action. The only login control lives in the **Identity (idM)** workspace widget, four sections below the top of the page. This is a **UX placement gap**, not a missing auth stack.

## Recommendation

Add a **Sign in via idM** control to the workspace header (or sidebar) that either opens the Identity widget or inlines the user picker + `idm_login` form, so unauthenticated operators can transition without scrolling.

## GitHub evidence

| Repository | Path | Role |
|---|---|---|
| identityManagement | `contracts/COPM-IDM-AUTH-CONTRACT.md` | Approved auth contract |
| identityManagement | `src/Application.php` | idM route registration |
| identityManagement | `src/Domain/Service/ActorContextService.php` | Actor resolution |
| coPilotManagement | `public/index.php` | `idm_login` / `idm_logout` handlers |
| coPilotManagement | `src-php/Services/IdmService.php` | commL-mediated auth |
| coPilotManagement | `src-php/Auth/SessionStore.php` | Presentation session |
| coPilotManagement | `templates/partials/idm-auth-panel.php` | Login UI |
| coPilotManagement | `templates/partials/current-user.php` | Guest / logout chrome |
| communicationLayer | `contracts/contracts.json` | Contract registry |
| communicationLayer | `routes/routes.json` | idM upstream route |
