# identityManagement (idM)

Status: Architecture Foundation
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

idM does not own canonical business-object identity, identity resolution, cross-domain identity mapping, or identifiers for animals, products, customers, contacts, publications, advertisements, inventory, hatch runs, or orders.

All cross-module communication must flow through communicationLayer (commL).

```text
Module -> communicationLayer (commL) -> Module
```

This repository currently provides the MVP architecture foundation, documentation set, local governance record, roadmap, and draft API notes for OK-Core review.

## Implementation Status

MVP IMPLEMENTATION FOUNDATION COMPLETE

The repository now contains a dependency-free PHP 8.3 MVP foundation with:

- Public JSON entrypoint in `public/index.php`
- Minimal router, request, response, and error handling
- PDO-based MariaDB repositories
- Domain services for idM-owned entities
- Audit logging for state-changing endpoints
- MariaDB 10.6 compatible migration SQL
- Manual curl test documentation

No Composer, NodeJS, npm, Docker, Redis, RabbitMQ, WebSockets, Python 3 runtime dependency, or long-running daemon is required.
