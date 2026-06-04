# identityManagement (idM) Governance

Status: Architecture Foundation
Module: identityManagement
Module code: idM
Governance authority: OK-Core

## Authority Order

1. OK-Core ADRs
2. OK-Core architecture/*
3. OK-Core architecture/MODULE-CATALOG.md
4. Local idM ADRs
5. Local idM documentation

Local idM documentation may clarify implementation details.

Local idM documentation may not override OK-Core governance.

## Governance Position

OK-Core accepted Option A.

identityManagement (idM) is an Access Identity module.

idM owns access subjects, roles, permissions, access policies, service accounts, and token references.

idM does not own canonical business-object identity, external identity mapping, identity resolution, or lifecycle/audit records for foreign domain objects.

## Communication Boundary

Mandatory:

```text
Module -> communicationLayer (commL) -> Module
```

Forbidden:

- Module -> Module
- Module -> Foreign Database
- Module -> Foreign Internal Code
- Shared Mutable Tables

## Deployment Constraints

idM targets the verified Versio baseline:

- PHP 8.3
- MariaDB 10.6
- HTTPS
- Cron
- SSH
- Git deployment

idM architecture must not depend on:

- NodeJS
- npm
- Docker
- RabbitMQ
- Redis
- WebSockets
- Python 3
- Long-running daemons

## API Governance

API drafts start in idM.

Canonical accepted API contracts belong in OK-Core after review.

Local draft location:

```text
docs/api/idm-api-draft.yaml
```

API status:

```text
DRAFT_IN_MODULE
```

## Governance Rule

idM may define access identity.

idM may not become a hidden owner of foreign data, canonical business-object identity, or cross-domain identity mapping.
