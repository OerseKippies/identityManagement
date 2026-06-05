# Changelog

## 2026-06-05

### OK-Core Submission

- Added `reviews/REVIEW-IDM-003-OKCORE-SUBMISSION.md`.
- Added `approval-request/RFA-IDM-003-OKCORE-APPROVAL.md`.
- Added `handover/OK-CORE-HANDOVER-IDM-MVP-RUNTIME-COMPLETE.md`.
- Added `docs/runtime-evidence/CORRELATION-EVIDENCE.md`.
- Status: READY FOR OK-CORE APPROVAL.

### MVP Runtime Build

- Added PHP 8.3 runtime (`public/api/index.php`, `src/*`).
- Added MariaDB migration `migrations/001_initial_schema.sql` and `scripts/migrate.php`.
- Implemented repositories, domain services, audit logging, and correlation support.
- Implemented v1 API endpoints from `docs/api/idm-api-draft.yaml`.
- Added unit tests (`tests/run.php`).
- Added CCM mandatory outputs: domain models, state models, ADRs, contracts, runtime evidence, review report, and approval request.
- Preserved Option A Access Identity scope; no canonical identity registry terms introduced.

## 2026-06-04

### Initial Repository Setup

- Created identityManagement repository foundation.
- Added governance and architecture documentation structure.

### Governance Decision

- Recorded OK-Core Option A decision.
- Confirmed idM as Access Identity module.
- Excluded canonical business-object identity and cross-domain identity mapping.

### Architecture Foundation

- Added ownership, non-ownership, deployment, communication, API, database, model, roadmap, and research foundation documents.

### DoD Gap Assessment

- Recorded formal DoD gap assessment as Major/Open.
- Identified missing handover, security, state diagrams, research, DoD validation, and audit retention documentation.

### DoD Closure Pass

- Completed security model, state models, research deliverables, audit model, root documentation, API governance notes, database governance, DoD validation, audit closure, readiness report, roadmap updates, and final OK-Core handover.
