# idM Versio Deployment

Status: MVP Implementation Foundation Complete

## Baseline

The implementation targets the accepted Versio baseline:

```text
PHP 8.3
MariaDB 10.6
HTTPS
Cron
SSH
Git deployment
```

## Steps

1. Deploy repository files.
2. Point the web root to `public/`.
3. Copy `config/config.php.example` to `config/config.php`.
4. Set the MariaDB DSN, username, and password.
5. Apply `database/migrations/001_create_idm_schema.sql` to the module-owned database.
6. Verify `GET /health`.

## Not Required

```text
Composer
NodeJS
npm
Docker
RabbitMQ
Redis
WebSockets
Python 3
Long-running daemons
```
