# idM Module Inventory

Status: Architecture Foundation
Module: identityManagement
Code: idM

## Classification

```text
Access Identity Module
VERSIO_HOSTED
DRAFT_IN_MODULE API status
```

## Owned Concepts

```text
User
Role
Permission
ServiceAccount
AccessPolicy
TokenReference
```

## Required Dependencies

```text
communicationLayer (commL)
OK-Core governance
MariaDB 10.6
PHP 8.3
HTTPS
Cron where scheduled maintenance is required
```

## Forbidden Dependencies

```text
Direct Module Access
Foreign Databases
Foreign Internal Code
Shared Mutable Tables
NodeJS
npm
Docker
RabbitMQ
Redis
WebSockets
Python 3
Long-running daemons
```
