# INITIAL-ADMINISTRATOR-CREATION

Date: 2026-06-07T23:04:21Z  
Repository: OerseKippies/identityManagement  
Authority: OerseKippies/OK-Core/START-HERE.md  
Classification: Runtime Evidence — Initial Platform Administrator (CCM)  
Path: copM consumer → commL → idM

## Result

```text
SUCCESS
```

## Mandatory Reading Compliance

| Document | Status |
|---|---|
| `OerseKippies/OK-Core/START-HERE.md` | READ |
| `identityManagement/runtime/evidence/IDM-ADMINISTRATION-MVP.md` | READ |
| `identityManagement/runtime/evidence/IDM-CURRENT-LOGIN-METHOD.md` | READ |
| `coPilotManagement/src-php/Auth/AuthVisibility.php` | READ (copM gating model) |

## Problem Statement

Production copM exposed identity administration screens gated by `admin`/`operator` role or `copm.identity.admin` permission. The probe seed user (`copm.probe`) had no roles or permissions, blocking validation of administration UI.

## Authorization Model Verified (Production)

| Actor | actorType | roles | permissions | Administration UI |
|---|---|---|---|---|
| copM Probe User | USER | (none) | (none) | Hidden |
| Platform Administrator | USER | `admin` | `copm.identity.admin`, `copm.diagnostics.view`, `copm.admin`, `copm.operator` | Visible |

copM gates via `AuthVisibility::showIdentityAdminSurfaces()` and `showOperatorSurfaces()` — UI only; idM actor context is source of truth.

## Administrator Created

| Field | Value |
|---|---|
| userId | `7300b60c-7387-435d-baee-bd8e836e7f93` |
| username | `admin` |
| email | `admin@oerse-kippies.nl` |
| displayName | Platform Administrator |
| status | ACTIVE |
| roleId | `20d7ca05-27ed-43c0-ae7d-71da5fc27e94` |
| roleCode | `admin` |

### Permissions assigned to `admin` role

- `copm.identity.admin` — User/Role/Permission/Assignments administration
- `copm.diagnostics.view` — Technical diagnostics surfaces
- `copm.admin` — Operator/admin navigation
- `copm.operator` — Operator navigation

## Probe User Integrity

| Check | Result |
|---|---|
| Probe user still ACTIVE | PASS |
| actorType remains USER | PASS |
| No admin role assigned | PASS |
| No admin permissions assigned | PASS |

## Verification

Scripts:

- `scripts/create_initial_administrator.ps1`
- `scripts/verify_initial_administrator.ps1`

| Check | Result |
|---|---|
| Admin user ACTIVE | PASS |
| Admin actor context includes `admin` role | PASS |
| Admin permissions include `copm.identity.admin` | PASS |
| Users list via commL | PASS |
| Roles list via commL | PASS |
| Permissions list via commL | PASS |
| Probe user unchanged (no escalation) | PASS |

Capture: `runtime/evidence/initial-administrator-capture.json`  
Logs: `runtime/evidence/initial-administrator-create.log`, `initial-administrator-verify.log`

## Sign-In (copM)

No password authentication exists (see `IDM-CURRENT-LOGIN-METHOD.md`). Sign in at https://copilot.oerse-kippies.nl with username **`admin`** or email **`admin@oerse-kippies.nl`**.

Required password change: **NO** (not applicable — presentation session via actor-context resolve).

## Assessment

| Dimension | Result |
|---|---|
| Boundary compliance (commL mediation) | PASS |
| Least privilege for probe user | PASS |
| Administration validation unblocked | PASS |
