# RFA-IDM-003-OKCORE-APPROVAL

Module: identityManagement (idM)
Request Type: Formal OK-Core MVP Runtime Approval
Date: 2026-06-05
Status: SUBMITTED
Result: READY FOR OK-CORE APPROVAL

## Request

Formal OK-Core evidence-based approval of:

```text
identityManagement (idM) — MVP Runtime Complete
```

## Governance Declaration

idM remains **Option A Access Identity module**.

Owned entities only:

```text
User
Role
Permission
ServiceAccount
AccessPolicy
TokenReference
```

Explicitly not owned:

```text
CanonicalIdentity
IdentityRegistry
IdentityMapping
ExternalIdentifier
IdentityResolution
IdentityLifecycle
Identity
IdentityReference
```

## Runtime Evidence (Verified)

| Evidence | Result | Path |
|---|---|---|
| PHP 8.3.31 | PASS | OerseKippies/identityManagement/docs/runtime-evidence/PHP-VERSION-EVIDENCE.md |
| Migrations | PASS | OerseKippies/identityManagement/docs/runtime-evidence/MIGRATION-EVIDENCE.md |
| Tests 8/8 | PASS | OerseKippies/identityManagement/docs/runtime-evidence/TEST-EVIDENCE.md |
| Health endpoint | PASS | OerseKippies/identityManagement/docs/runtime-evidence/HEALTH-ENDPOINT-EVIDENCE.md |
| Audit logging | PASS | OerseKippies/identityManagement/docs/runtime-evidence/AUDIT-EVIDENCE.md |
| correlationId | PASS | OerseKippies/identityManagement/docs/runtime-evidence/CORRELATION-EVIDENCE.md |
| Governance | PASS | OerseKippies/identityManagement/docs/runtime-evidence/GOVERNANCE-RUNTIME-EVIDENCE.md |

## Submission Package

| Document | Path |
|---|---|
| OK-Core handover | OerseKippies/identityManagement/handover/OK-CORE-HANDOVER-IDM-MVP-RUNTIME-COMPLETE.md |
| OK-Core submission review | OerseKippies/identityManagement/reviews/REVIEW-IDM-003-OKCORE-SUBMISSION.md |
| Runtime verification review | OerseKippies/identityManagement/reviews/REVIEW-IDM-002-RUNTIME-VERIFICATION.md |
| MVP runtime review | OerseKippies/identityManagement/reviews/REVIEW-IDM-001-MVP-RUNTIME.md |
| Runtime entry | OerseKippies/identityManagement/public/api/index.php |
| Application | OerseKippies/identityManagement/src/Application.php |
| Migrations | OerseKippies/identityManagement/migrations/001_initial_schema.sql |
| API draft | OerseKippies/identityManagement/docs/api/idm-api-draft.yaml |
| ADR | OerseKippies/identityManagement/ADRs/ADR-LOCAL-0001.md |
| Contracts | OerseKippies/identityManagement/contracts/COMMUNICATION-CONTRACTS.md |

## Prerequisite

```text
communicationLayer (commL) — APPROVED
```

## Traceability Chain

```text
Architecture Foundation (APPROVED)
  -> MVP Runtime Build
  -> Runtime Verification (PASS)
  -> OK-Core Submission (this request)
  -> Evidence package
  -> Commit history
```

## Requested OK-Core Outcome

Upon approval, record:

```text
OerseKippies/OK-Core/approvals/records/APPROVAL-IDM-MVP-RUNTIME.md
```

Update:

```text
OerseKippies/OK-Core/approvals/records/INDEX.md
```

## Approval Lifecycle Requested

```text
SUBMITTED
-> READY FOR OK-CORE APPROVAL
-> IN REVIEW
-> APPROVED
```
