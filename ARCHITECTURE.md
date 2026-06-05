# identityManagement Architecture

Status: MVP Runtime Complete

## High-Level Architecture

identityManagement (idM) is the Access Identity module for the OK ecosystem.

Owned concepts:

```text
User
Role
Permission
ServiceAccount
AccessPolicy
TokenReference
```

## Runtime Components

```text
public/api/index.php          HTTP entry
src/Application.php           Routing, auth, error handling
src/Domain/Service/*          Business logic and audit orchestration
src/Repository/*              MariaDB persistence
src/Audit/*                   Audit logging
migrations/001_initial_schema.sql
```

## API

Version: `v1` (DRAFT_IN_MODULE)

OpenAPI: `docs/api/idm-api-draft.yaml`, `public/api/idm-api-draft.yaml`

## Cross-Cutting Concerns

| Concern | Implementation |
|---|---|
| Correlation | `X-Correlation-Id` header, UUID v4, stored in audit |
| Audit | Append-only `idm_audit_log` on every mutation |
| Errors | Standard JSON error model with `correlationId` |
| Security | API key gate (configurable), actor headers for audit |
| Versioning | `/v1/*` routes, `X-Api-Version: v1` |

## Boundary

```text
Module -> communicationLayer (commL) -> Module
```

Forbidden:

```text
Module -> Module direct
Canonical identity registry ownership
Cross-domain identity mapping
```

## Document Map

```text
DATABASE-SCHEMA.md
PERSISTENCE-STRATEGY.md
AUDIT-MODEL.md
SECURITY-MODEL.md
OPERATIONS-MODEL.md
DEPLOYMENT.md
VERSIO-COMPLIANCE.md
architecture/*                Foundation documentation
docs/domain-models/*
docs/state-models/*
```

## Deployment

```text
PHP 8.3
MariaDB 10.6
HTTPS
VERSIO_HOSTED
```

No Composer, NodeJS, Docker, Redis, RabbitMQ, or long-running daemons.
