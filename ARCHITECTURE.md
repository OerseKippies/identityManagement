# identityManagement Architecture

Status: Architecture Foundation Complete

## High-Level Architecture

identityManagement (idM) is the Access Identity module for the OK ecosystem.

It owns access identity concepts only:

```text
User
Role
Permission
ServiceAccount
AccessPolicy
TokenReference
```

## Document Map

```text
architecture/MODULE-INVENTORY.md
architecture/OWNERSHIP-MATRIX.md
architecture/NON-OWNERSHIP-MATRIX.md
architecture/SECURITY-MODEL.md
architecture/STATUS-TRANSITIONS.md
architecture/AUDIT-LOGGING.md
architecture/DATABASE-BOUNDARY.md
architecture/API-GOVERNANCE-ALIGNMENT.md
architecture/DOD-VALIDATION.md
```

## Component Overview

Planned MVP implementation components:

```text
idM API draft
idM domain services
idM-owned MariaDB schema
idM audit logging
idM security policies
```

No runtime code is created by the DoD closure pass.

## Boundary Summary

Mandatory:

```text
Module -> communicationLayer (commL) -> Module
```

Forbidden:

```text
Module -> Module
Module -> Foreign Database
Module -> Foreign Internal Code
Shared Mutable Tables
Canonical business-object identity ownership
```

## Deployment Summary

Classification:

```text
VERSIO_HOSTED
```

Baseline:

```text
PHP 8.3
MariaDB 10.6
HTTPS
Cron
SSH
Git deployment
```
