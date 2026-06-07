# IDM-ADMINISTRATION-MVP-HANDOVER

Date: 2026-06-07  
Module: identityManagement (idM)  
Classification: Runtime — Identity Administration MVP  
Authority: OerseKippies/OK-Core/START-HERE.md  
Status: **APPROVED**

---

## Executive Summary

Identity administration MVP enables operators to create and manage users, roles, permissions, and RBAC assignments through idM v1 API endpoints. Administratively created users resolve through the existing actor-context login flow (`subjectHint` = user UUID) and appear in copM user picker after enable.

**Deployed to Versio:** `d5099f3` @ https://idm.oerse-kippies.nl  
**Post-deploy validation:** 15/15 PASS

---

## Delivered Capabilities

| Capability | API Surface |
|---|---|
| Create / list / view / update users | `/v1/users` |
| User lifecycle | `/v1/users/{id}/enable`, `/disable`, `/lock`, `/unlock` |
| Create / list / view / update / disable roles | `/v1/roles` |
| Create / list / view / update permissions | `/v1/permissions` |
| User-role assignment | `POST/DELETE /v1/users/{id}/roles/{id}` |
| Role-permission assignment | `POST/DELETE /v1/roles/{id}/permissions/{id}` |
| Audit on mutations | `idm_audit_log` |
| Audit query | `GET /v1/audit-log?correlationId=` |
| Login resolution | `POST /v1/identity/actor-context` |

---

## Operator Workflow

```text
1. POST /v1/users          → create user (status PENDING)
2. POST /v1/users/{id}/enable → activate for login
3. POST /v1/roles          → create role (optional)
4. POST /v1/permissions    → create permission (optional)
5. POST /v1/users/{id}/roles/{roleId}           → assign role
6. POST /v1/roles/{roleId}/permissions/{permId} → assign permission
7. copM user picker        → lists users via idM.users.list.v1
8. Login                   → subjectHint = userId UUID
```

Direct API access requires `X-Api-Key`. commL mediation bypasses API key for registered contracts only (login paths).

---

## Deploy Record

| Field | Value |
|---|---|
| Host | vserver423.axc.eu (Versio) |
| Path | `~/domains/idm.oerse-kippies.nl/identityManagement` |
| Commit | `d5099f3` |
| Method | `git fetch && git reset --hard origin/main` |
| Migration | `001_initial_schema already applied` |
| Validated | `scripts/idm_administration_mvp_validate.ps1` — 15/15 PASS |

---

## Evidence Package

| Document | Path |
|---|---|
| Administration MVP evidence | `runtime/evidence/IDM-ADMINISTRATION-MVP.md` |
| Production capture JSON | `runtime/evidence/idm-administration-mvp-capture.json` |
| Login method (updated) | `runtime/evidence/IDM-CURRENT-LOGIN-METHOD.md` |
| Review | `reviews/REVIEW-IDM-ADMINISTRATION-MVP.md` |
| Validation script | `scripts/idm_administration_mvp_validate.ps1` |

---

## Out of Scope

- Device-based authentication (`roadmap/AUTH-BACKLOG-001-DEVICE-AUTHENTICATION.md`)
- Username/email login (not implemented)
- Password verification
- copM administration UI (idM API only)

---

## Boundary Confirmation

idM owns: User, Role, Permission, ServiceAccount, AccessPolicy, TokenReference.

idM does not own canonical identity, identity mapping, or cross-domain identity resolution.

---

## Review Decision

```text
APPROVED
Review Status: PASS
```
