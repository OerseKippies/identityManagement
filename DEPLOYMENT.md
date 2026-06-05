# idM Deployment

Status: MVP Runtime
Classification: VERSIO_HOSTED

## Baseline

```text
PHP 8.3
MariaDB 10.6
HTTPS
Cron (optional post-MVP maintenance)
SSH
Git deployment
```

## Entry Point

```text
public/api/index.php
```

## Setup

1. Copy `config/config.example.php` to `config/config.php`
2. Create MariaDB database and user
3. Run `php scripts/migrate.php`
4. Configure HTTPS virtual host to `public/api`
5. Set `api.api_key` in configuration

## Local Development

```text
php -S localhost:8080 -t public/api
```

## Prohibited Dependencies

```text
Composer (not used)
NodeJS / npm
Docker
Redis
RabbitMQ
WebSockets
Python runtime
Long-running daemons
```

## Architecture Reference

`architecture/DEPLOYMENT.md`, `architecture/IMPLEMENTATION-CONSTRAINTS.md`
