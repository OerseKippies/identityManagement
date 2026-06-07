# INITIAL-ADMINISTRATOR-HANDOVER

Date: 2026-06-07  
Repository: OerseKippies/identityManagement (+ copM consumer)  
Module: idM (identity owner)

## Summary

Initial platform administrator provisioned in production idM for copM administration validation. Probe user remains unprivileged.

## Administrator Credentials

| Field | Value |
|---|---|
| Username | `admin` |
| Email | `admin@oerse-kippies.nl` |
| Display name | Platform Administrator |
| userId | `7300b60c-7387-435d-baee-bd8e836e7f93` |
| Status | ACTIVE |
| Role | `admin` (Platform Administrator) |

## Sign-In at copM

1. Open https://copilot.oerse-kippies.nl
2. Navigate to **Identity** (Workspace group)
3. Enter **`admin`** or **`admin@oerse-kippies.nl`** in Sign in
4. After sign-in, **Business — Admin** and **Administration** nav groups appear

**Temporary login identifier:** `admin` (username) or `admin@oerse-kippies.nl` (email)

**Required password change:** NO — password authentication is not implemented; sign-in resolves actor context via commL.

## Permissions Granted

Via role `admin`:

- `copm.identity.admin`
- `copm.diagnostics.view`
- `copm.admin`
- `copm.operator`

## Probe User (unchanged)

| Field | Value |
|---|---|
| Username | `copm.probe` |
| userId | `bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb` |
| actorType | USER |
| Roles | none |
| Permissions | none |

## Re-provision / Verify

```powershell
cd identityManagement
.\scripts\create_initial_administrator.ps1   # idempotent
.\scripts\verify_initial_administrator.ps1
```

## Evidence

- `runtime/evidence/INITIAL-ADMINISTRATOR-CREATION.md`
- `reviews/REVIEW-INITIAL-ADMINISTRATOR-CREATION.md`

## Related

- copM identity admin UI: `coPilotManagement/handover/COPM-IDENTITY-ADMINISTRATION-HANDOVER.md`
- idM administration API: `runtime/evidence/IDM-ADMINISTRATION-MVP.md`
