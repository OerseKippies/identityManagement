# idM Deployment

Status: Architecture Foundation
Classification: VERSIO_HOSTED
Authority: OK-Core deployment governance

## Baseline

identityManagement targets the accepted Versio baseline:

```text
PHP 8.3
MariaDB 10.6
HTTPS APIs
Cron
SSH
Git deployment
```

## Runtime Shape

The MVP should be implementable as a PHP 8.3 web module backed by a module-owned MariaDB schema.

Cron may be used for scheduled token-reference cleanup, status maintenance, or retention jobs after those requirements are accepted.

## Non-Baseline Components

idM must not require:

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

## Deployment Principle

Deployment location does not change ownership.

idM owns only idM access identity data, even when other modules consume idM access decisions through communicationLayer (commL).
