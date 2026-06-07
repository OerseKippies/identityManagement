# idM Authentication Flow — Runtime Evidence

**Authority:** APR-2026-06-07-030  
**Captured:** 2026-06-07T14:50–14:56 UTC  
**Target:** Versio production (`vserver423.axc.eu`)

## Environment

| Service | URL | Status |
|---|---|---|
| copM | `https://copilot.oerse-kippies.nl/` | Reachable |
| commL | `https://comml.oerse-kippies.nl/` | Reachable |
| idM | `https://idm.oerse-kippies.nl/` | Reachable (health OK) |

## E1 — idM health

```bash
curl -sk https://idm.oerse-kippies.nl/v1/health
```

```json
{"status":"healthy","module":"identityManagement","moduleCode":"idM","version":"v1","timestamp":"2026-06-07T14:51:58Z"}
```

## E2 — idM users list (commL-mediated headers)

```bash
curl -sk -H 'X-Source-Module: communicationLayer' \
  -H 'X-Correlation-Id: probe-users' \
  https://idm.oerse-kippies.nl/api/v1/identity/users
```

```json
{"items":[{"userId":"bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb","username":"copm.probe","displayName":"copM Probe User","email":"copm.probe@example.test","status":"ACTIVE","createdAt":"2026-06-06 14:23:17","updatedAt":"2026-06-06 14:23:17"}]}
```

**Outcome:** Login endpoint data source available; probe user seeded (`migrations/002_copm_probe_seed.sql`).

## E3 — copM unauthenticated page (GET)

```bash
curl -sk https://copilot.oerse-kippies.nl/ | grep -E 'workspace-shell__auth|Guest|Login via commL'
```

**Observed fragments:**

- `workspace-shell__auth--unauthenticated`
- `Guest` / `Not signed in via idM`
- `Login via commL` (inside `data-copm-widget="idm-auth"` panel, not in header)
- `idm-auth` widget at display order 3 in default layout JSON

**Outcome:** Login UI exists in HTML but is not in header chrome.

## E4 — Login transition (UNAUTHENTICATED → AUTHENTICATED)

```bash
rm -f /tmp/cj.txt /tmp/out.html
curl -sk -c /tmp/cj.txt https://copilot.oerse-kippies.nl/ -o /dev/null
curl -sk -b /tmp/cj.txt -c /tmp/cj.txt -X POST \
  -d 'copm_action=idm_login&user_id=bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb' \
  https://copilot.oerse-kippies.nl/ -o /tmp/out.html
grep -E 'workspace-shell__auth|current-user__name' /tmp/out.html | head -6
```

**Observed:**

```
workspace-shell__auth--authenticated
current-user__name">copM Probe User
authenticated
copM Probe User
```

**Counts:** `authenticated` ×5, `Logout` ×3 in response HTML.

**Outcome:** Session creation and actor binding succeed; full auth flow PASS.

## E5 — Logout transition (AUTHENTICATED → UNAUTHENTICATED)

```bash
curl -sk -b /tmp/cj.txt -c /tmp/cj.txt -X POST \
  -d 'copm_action=idm_logout' \
  https://copilot.oerse-kippies.nl/ -o /tmp/out2.html
grep 'workspace-shell__auth' /tmp/out2.html | head -2
```

**Observed:**

```
workspace-shell__auth--unauthenticated
```

**Counts:** `unauthenticated` ×6, `Guest` ×2.

**Outcome:** Logout clears presentation session; state returns to guest.

## E6 — commL contract registry (static)

From `communicationLayer/contracts/contracts.json` at commit `0f5632f`:

- `idM.users.list.v1` — GET `/api/v1/identity/users`, consumer includes `coPilotManagement`
- `idM.actorContext.resolve.v1` — POST `/api/v1/identity/actor-context`, consumer includes `coPilotManagement`

From `communicationLayer/routes/routes.json`:

```json
"identityManagement": {
  "shortCode": "idM",
  "baseUrl": "https://idm.oerse-kippies.nl",
  "healthEndpoint": "/health",
  "status": "active"
}
```

## E7 — commL audit trail (production)

`communicationLayer/storage/audit/audit-2026-06-07.jsonl` — sample entries:

```json
{"eventType":"ROUTE_COMPLETED","sourceModule":"coPilotManagement","targetModule":"identityManagement","contractId":"idM.users.list.v1","httpStatus":200,"targetStatus":200}
```

**Outcome:** copM → commL → idM users list routing verified in production audit log.

## E8 — Implementation anchors (code)

### idM routes (`identityManagement/src/Application.php`)

```php
$router->add('GET', '/v1/identity/users', ...);
$router->add('POST', '/v1/identity/actor-context', ...);
```

### copM login handler (`coPilotManagement/public/index.php`)

```php
if ($action === 'idm_login') {
    $userId = (string) ($_POST['user_id'] ?? '');
    $idmService->loginWithUser($userId);
} elseif ($action === 'idm_logout') {
    $idmService->logoutPresentation();
}
```

### copM session write (`coPilotManagement/src-php/Auth/SessionStore.php`)

```php
$_SESSION[self::SESSION_KEY] = [
    'presentationSessionId' => $presentationSessionId,
    'authenticatedAt' => gmdate('c'),
    'actorContext' => $actorContext,
    'correlationId' => $correlationId,
];
```

## Token issuance / validation (MVP note)

| Mechanism | MVP login | Evidence |
|---|---|---|
| Presentation session UUID | Yes — created on login | E4 |
| Actor context in PHP session | Yes — validated on read | E4, `SessionStore::read()` |
| idM TokenReference JWT | No — not in copM login path | `ActorContextService` returns `tokenReferenceId: null` for USER |

## Summary table

| Check | Evidence | Result |
|---|---|---|
| Login endpoint | E1, E2, E8 | PASS |
| commL route | E6, E7 | PASS |
| Login UI | E3 | PASS (discoverability gap) |
| Session creation | E4 | PASS |
| Auth flow E2E | E4, E5 | PASS |
| Logout | E5 | PASS |

## Repository HEAD at audit time

| Repo | SHA | Message |
|---|---|---|
| identityManagement | `ded9b058` | Adopt MODULE-COMPLIANCE for Phase 9C |
| coPilotManagement | `6e1f568` | F-MVP-RUNTIME-003 runtime classification fix |
| communicationLayer | `0f5632f` | Close F-PDR-N02 naming + MODULE-COMPLIANCE |

Evidence files committed in identityManagement after this capture (see audit report commit SHA).
