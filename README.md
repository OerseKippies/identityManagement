# identityManagement (idM)

Status: MVP Runtime APPROVED
Module code: idM
Authority: OK-Core
Deployment classification: VERSIO_HOSTED

identityManagement (idM) is the OK ecosystem Access Identity module.

idM owns only access identity data:

- User
- Role
- Permission
- ServiceAccount
- AccessPolicy
- TokenReference

All cross-module communication must flow through communicationLayer (commL).

```text
Module -> communicationLayer (commL) -> Module
```

## Runtime

PHP 8.3 MVP runtime with MariaDB 10.6 persistence.

```text
public/api/index.php   API entry point
src/                   Application code
migrations/            Database schema
scripts/migrate.php    Migration runner
tests/run.php          Unit tests
```

## Quick Start

1. Copy `config/config.example.php` to `config/config.php`
2. Configure MariaDB credentials and API key
3. Run `php scripts/migrate.php`
4. Serve `public/api` over HTTPS (or `php -S localhost:8080 -t public/api` for local use)
5. Verify `GET /v1/health`

## Repository Map

```text
public/api/      HTTP API entry and OpenAPI draft
src/             Runtime implementation
migrations/      MariaDB migrations
config/          Runtime configuration
docs/            Domain models, state models, runtime evidence, tests
contracts/       commL contract mapping
reviews/         MVP runtime review report
approval-request/ OK-Core approval request
ADRs/            Architectural decision records
architecture/    Architecture foundation documentation
database/        Table design documentation
governance/      Governance records
handover/        OK-Core handover packages
research/        Accepted MVP foundation research
roadmap/         Active work and MVP plan
```

## Submission

- Handover: `handover/OK-CORE-HANDOVER-IDM-MVP-RUNTIME-COMPLETE.md`
- Review: `reviews/REVIEW-IDM-003-OKCORE-SUBMISSION.md`
- Approval request: `approval-request/RFA-IDM-003-OKCORE-APPROVAL.md`

## Governance

Option A Access Identity boundary is preserved. idM does not implement canonical identity registry concepts.
