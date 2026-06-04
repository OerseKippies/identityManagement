# identityManagement (idM)

Status: Architecture Foundation Complete
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

This repository provides the completed architecture foundation, documentation set, local governance record, roadmap, and draft API notes for OK-Core review.

## Ownership Summary

Owned:

```text
User
Role
Permission
ServiceAccount
AccessPolicy
TokenReference
```

Not owned:

```text
Canonical identity concepts
Business-object identity concepts
Foreign module data
Foreign module workflows
Cross-domain identity mapping
```

## Repository Map

```text
architecture/  Architecture, security, audit, database boundary and DoD validation
database/      MariaDB table design documentation
docs/api/      DRAFT_IN_MODULE API draft and notes
governance/    Local governance and ADRs
handover/      OK-Core handover package
research/      Accepted MVP foundation research
roadmap/       Active work, MVP plan and backlog
audit/         DoD gap assessment and closure record
```

## Current Readiness Status

```text
Architecture Foundation Complete
MVP Ready For Implementation
```

No PHP runtime implementation is included in this closure pass.
