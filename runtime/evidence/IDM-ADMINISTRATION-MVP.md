# IDM-ADMINISTRATION-MVP

Date: 2026-06-07T22:13:00Z  
Repository: OerseKippies/identityManagement  
Authority: OerseKippies/OK-Core/START-HERE.md  
Target: https://idm.oerse-kippies.nl  
Classification: Runtime Evidence — Identity Administration MVP (CCM)

## Result

```text
SUCCESS
```

Operational identity administration is implemented and verified on production for users, roles, permissions, assignments, lifecycle transitions, audit writes, correlation propagation, and login resolution for administratively created users.

---

## Mandatory Reading Compliance

| Document | Status |
|---|---|
| `OerseKippies/OK-Core/START-HERE.md` | READ |
| `implementation/MVP-SCOPE.md` | READ (via START-HERE §6) |
| `governance/APPROVAL-PROCESS.md` | READ (via START-HERE §8) |
| `MODULE-SCOPE.md` | READ |
| `contracts/COPM-IDM-AUTH-CONTRACT.md` | READ |
| `roadmap/AUTH-BACKLOG-001-DEVICE-AUTHENTICATION.md` | READ (out of scope) |

---

## Implementation Summary

| Area | Endpoint(s) | Status |
|---|---|---|
| Users CRUD + lifecycle | `POST/GET/PATCH /v1/users`, `/enable`, `/disable`, `/lock`, `/unlock` | PASS (production) |
| Roles CRUD + disable | `POST/GET/PATCH /v1/roles`, `/disable` | PASS (production) |
| Permissions CRUD | `POST/GET/PATCH /v1/permissions` | PASS (production) |
| Assignments | `POST/DELETE /v1/users/{id}/roles/{id}`, `/v1/roles/{id}/permissions/{id}` | PASS (production) |
| Audit on mutations | `idm_audit_log` via `AuditLogger` | PASS (production) |
| Audit query | `GET /v1/audit-log?correlationId=` | PASS (production) |
| Correlation | `X-Correlation-Id` on all responses | PASS (production) |
| Login enablement | `POST /v1/identity/actor-context` with `subjectHint` UUID | PASS (new user) |

Source: `src/Application.php`, `src/Domain/Service/*`, `scripts/idm_administration_mvp_validate.ps1`

Validation capture: `runtime/evidence/idm-administration-mvp-capture.json`

---

## Created User Example

**Request:**

```http
POST /v1/users HTTP/1.1
Host: idm.oerse-kippies.nl
Content-Type: application/json
X-Api-Key: <configured>
X-Correlation-Id: bb716d22-1dbd-4114-aed1-e37865faed4e
X-Actor-Type: SYSTEM
```

```json
{
  "username": "admin.mvp.0e7634ad",
  "displayName": "Admin MVP User 0e7634ad",
  "email": "admin.mvp.0e7634ad@example.test"
}
```

**Response (HTTP 201):**

```json
{
  "userId": "c2e331b9-8f62-41f0-bc05-e249d582543a",
  "username": "admin.mvp.0e7634ad",
  "displayName": "Admin MVP User 0e7634ad",
  "email": "admin.mvp.0e7634ad@example.test",
  "status": "PENDING",
  "createdAt": "2026-06-07 22:12:11",
  "updatedAt": "2026-06-07 22:12:11"
}
```

**Operational note:** New users start as `PENDING`. Call `POST /v1/users/{userId}/enable` before login picker use.

---

## Listed Users Example

**Request:** `GET /v1/users`  
**Correlation-Id:** `34c536fd-5e6e-41f9-b72c-35494b02877e`

**Response excerpt (HTTP 200):**

```json
{
  "items": [
    {
      "userId": "c2e331b9-8f62-41f0-bc05-e249d582543a",
      "username": "admin.mvp.0e7634ad",
      "displayName": "Admin MVP User 0e7634ad",
      "email": "admin.mvp.0e7634ad@example.test",
      "status": "ACTIVE"
    },
    {
      "userId": "bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb",
      "username": "copm.probe",
      "displayName": "copM Probe User",
      "email": "copm.probe@example.test",
      "status": "ACTIVE"
    }
  ]
}
```

Production now has multiple administratively created users in addition to the probe seed user.

---

## Role Creation Example

**Correlation-Id:** `197c4635-58b6-4b69-9451-320f8b59d598`

```json
{
  "roleId": "4600e33d-b29a-4927-ad53-88f4b8fd0cfa",
  "roleCode": "admin.mvp.0e7634ad",
  "roleName": "Admin MVP Role 0e7634ad",
  "description": "CCM administration MVP validation role",
  "status": "ACTIVE",
  "createdAt": "2026-06-07 22:12:22",
  "updatedAt": "2026-06-07 22:12:22"
}
```

---

## Permission Creation Example

**Correlation-Id:** `26d1092e-7a3b-4340-a31c-786ade26945b`

```json
{
  "permissionId": "8bf89f69-f515-41b1-8a5a-7c653ebeafe7",
  "permissionCode": "admin.mvp.0e7634ad",
  "permissionName": "Admin MVP Permission 0e7634ad",
  "description": "CCM administration MVP validation permission",
  "status": "ACTIVE",
  "createdAt": "2026-06-07 22:12:25",
  "updatedAt": "2026-06-07 22:12:25"
}
```

---

## User-Role Assignment Example

**Request:** `POST /v1/users/c2e331b9-8f62-41f0-bc05-e249d582543a/roles/4600e33d-b29a-4927-ad53-88f4b8fd0cfa`  
**Correlation-Id:** `21da6c9f-308c-4d82-a335-662148ada9db`  
**Response:** HTTP 204 No Content

Audit action: `ASSIGN_ROLE` on entity `User` with `detailsJson.roleId`.

---

## Role-Permission Assignment Example

**Request:** `POST /v1/roles/4600e33d-b29a-4927-ad53-88f4b8fd0cfa/permissions/8bf89f69-f515-41b1-8a5a-7c653ebeafe7`  
**Correlation-Id:** `ae6f9e75-ff66-492a-aed5-6c6b47f36fd1`  
**Response:** HTTP 204 No Content

Audit action: `ASSIGN_PERMISSION` on entity `Role` with `detailsJson.permissionId`.

---

## Audit Record Example

Mutation correlation from user create: `ec37a4be-60ce-485c-bed0-2aa58c4546fe`

**Query:** `GET /v1/audit-log?correlationId=ec37a4be-60ce-485c-bed0-2aa58c4546fe`

**Response (HTTP 200):**

```json
{
  "items": [
    {
      "auditId": "83a5a335-ca37-43e9-a4f2-d27080825c64",
      "entityType": "User",
      "entityId": "ac851695-33d7-454d-97b0-a448d7575f5f",
      "action": "CREATE_USER",
      "actorType": "SYSTEM",
      "actorId": null,
      "correlationId": "ec37a4be-60ce-485c-bed0-2aa58c4546fe",
      "timestamp": "2026-06-07 22:18:02",
      "detailsJson": "{\"username\":\"admin.mvp.96d24acb\"}"
    }
  ]
}
```

---

## CorrelationId Example

Every API response includes:

```text
X-Correlation-Id: <uuid-v4>
X-Api-Version: v1
```

Example from actor-context resolve for newly created user:

```text
X-Correlation-Id: f059dfe6-b7bb-4b2d-bb8f-acad7bc7ed2e
```

Request may supply `X-Correlation-Id`; idM generates UUID v4 when absent (`src/Infrastructure/Correlation.php`).

---

## ActorContext Resolve — Newly Created User

**Request:**

```http
POST /v1/identity/actor-context HTTP/1.1
X-Source-Module: communicationLayer
X-Correlation-Id: f059dfe6-b7bb-4b2d-bb8f-acad7bc7ed2e
Content-Type: application/json
```

```json
{
  "credentialType": "USER",
  "subjectHint": "c2e331b9-8f62-41f0-bc05-e249d582543a"
}
```

**Response (HTTP 200):**

```json
{
  "actorType": "USER",
  "actorId": "c2e331b9-8f62-41f0-bc05-e249d582543a",
  "displayName": "Admin MVP User 0e7634ad (updated)",
  "status": "ACTIVE",
  "roles": ["admin.mvp.0e7634ad"],
  "permissions": ["admin.mvp.0e7634ad"],
  "tokenReferenceId": null,
  "correlationId": "f059dfe6-b7bb-4b2d-bb8f-acad7bc7ed2e"
}
```

Login mechanism unchanged: `subjectHint` must be user UUID. Username and email are not accepted.

---

## Ownership Confirmation

idM owns only: User, Role, Permission, ServiceAccount, AccessPolicy, TokenReference.

Not introduced: CanonicalIdentity, IdentityRegistry, IdentityMapping, ExternalIdentifier, cross-domain identity mapping.

Device authentication remains backlog only: `roadmap/AUTH-BACKLOG-001-DEVICE-AUTHENTICATION.md`

---

## Validation Script

```powershell
powershell -ExecutionPolicy Bypass -File scripts/idm_administration_mvp_validate.ps1
```

Requires `IDM_API_KEY` (env or `config/env.versio`, never committed).

---

## Versio Deploy

| Step | Result | Detail |
|---|---|---|
| Git push | PASS | `d5099f3` on `origin/main` |
| Versio pull | PASS | `~/domains/idm.oerse-kippies.nl/identityManagement` @ `d5099f3` |
| Migration | PASS | `001_initial_schema already applied` |
| Post-deploy validation | PASS | 15/15 steps (`idm_administration_mvp_validate.ps1`) |

Deployed: 2026-06-07T22:18 UTC

---

## GitHub Evidence

| Field | Value |
|---|---|
| File | `runtime/evidence/IDM-ADMINISTRATION-MVP.md` |
| Capture | `runtime/evidence/idm-administration-mvp-capture.json` |
| Commit | `d5099f3` |
| Deploy SHA | `d5099f3` |
| Repository | https://github.com/OerseKippies/identityManagement |
