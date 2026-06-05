# idM Versio Compliance

Status: MVP Runtime
Classification: VERSIO_HOSTED

## Required Baseline

| Requirement | idM Compliance | Evidence |
|---|---|---|
| PHP 8.3 | Yes | `public/api/index.php`, `src/*` |
| MariaDB 10.6 | Yes | `migrations/001_initial_schema.sql` |
| HTTPS | Yes | Deployment via HTTPS virtual host |
| Cron | Optional | No daemon required for MVP |
| SSH | Yes | Standard Versio SSH deployment |
| Git deployment | Yes | Repository-based deployment |

## Prohibited Components

| Component | Used | Evidence |
|---|---|---|
| Docker | No | No Dockerfile |
| Redis | No | No Redis client |
| RabbitMQ | No | No message broker |
| NodeJS / npm | No | No package.json |
| Python runtime | No | No Python dependency |
| Composer | No | No composer.json |
| Long-running daemon | No | Request/response PHP only |

## Communication Boundary

All cross-module access routes through communicationLayer (commL). Direct module-to-module calls are not implemented.

## Research Reference

`research/RES-001-VERSIO-CONSTRAINTS.md`
