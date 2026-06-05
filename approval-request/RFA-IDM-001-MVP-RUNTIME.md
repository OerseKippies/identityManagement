# RFA-IDM-001-MVP-RUNTIME

Module: identityManagement (idM)
Request Type: MVP Runtime Approval
Status: SUPERSEDED BY RFA-IDM-003-OKCORE-APPROVAL
Date: 2026-06-05

## Objective

Request OK-Core evidence-based approval for idM MVP Runtime Complete status.

## Governance Confirmation

idM remains Option A Access Identity module. Owned entities:

```text
User
Role
Permission
ServiceAccount
AccessPolicy
TokenReference
```

No canonical identity registry concepts are introduced.

## Evidence References

| Area | Path |
|---|---|
| Runtime entry | OerseKippies/identityManagement/public/api/index.php |
| Application router | OerseKippies/identityManagement/src/Application.php |
| Migrations | OerseKippies/identityManagement/migrations/001_initial_schema.sql |
| API draft | OerseKippies/identityManagement/docs/api/idm-api-draft.yaml |
| Public API spec | OerseKippies/identityManagement/public/api/idm-api-draft.yaml |
| Audit model | OerseKippies/identityManagement/AUDIT-MODEL.md |
| Security model | OerseKippies/identityManagement/SECURITY-MODEL.md |
| Database schema | OerseKippies/identityManagement/DATABASE-SCHEMA.md |
| Persistence | OerseKippies/identityManagement/PERSISTENCE-STRATEGY.md |
| Deployment | OerseKippies/identityManagement/DEPLOYMENT.md |
| Versio compliance | OerseKippies/identityManagement/VERSIO-COMPLIANCE.md |
| Domain models | OerseKippies/identityManagement/docs/domain-models/ |
| State models | OerseKippies/identityManagement/docs/state-models/ |
| Runtime evidence | OerseKippies/identityManagement/docs/runtime-evidence/ |
| Tests | OerseKippies/identityManagement/tests/ |
| Review report | OerseKippies/identityManagement/reviews/REVIEW-IDM-001-MVP-RUNTIME.md |
| ADR | OerseKippies/identityManagement/ADRs/ADR-LOCAL-0001.md |
| Contracts | OerseKippies/identityManagement/contracts/COMMUNICATION-CONTRACTS.md |

## Prerequisite

communicationLayer (commL): APPROVED (Foundation Module + MVP Runtime)

## Requested Outcome

APPROVAL record:

```text
OerseKippies/OK-Core/approvals/records/APPROVAL-IDM-MVP-RUNTIME.md
```
