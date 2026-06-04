# idM Implementation Constraints

Status: Architecture Foundation

## Required Platform Fit

The implementation must fit:

```text
PHP 8.3
MariaDB 10.6
HTTPS
Cron
SSH
Git deployment
```

## Prohibited Baseline Dependencies

```text
NodeJS
npm
Docker
RabbitMQ
Redis
WebSockets
Python 3
Long-running daemons
```

## Design Constraints

- Keep business logic inside idM only for access identity.
- Route cross-module calls through communicationLayer (commL).
- Use idM-owned database tables only.
- Keep API contracts as DRAFT_IN_MODULE until OK-Core acceptance.
- Avoid building a full OAuth platform in the MVP.
- Store TokenReference metadata, not plain token secrets.
