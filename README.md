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
