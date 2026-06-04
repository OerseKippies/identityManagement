# RES-001: Versio Constraints

Status: COMPLETE
Decision: ACCEPTED FOR MVP FOUNDATION

## Question

Which hosting constraints shape the idM MVP architecture?

## Findings

OK-Core proves PHP 8.3, MariaDB 10.6, HTTPS, Cron, SSH and Git deployment on Versio.

NodeJS, npm, Docker, Redis, RabbitMQ, WebSockets, Python 3 and long-running daemons are not baseline capabilities.

## OK-Core Impact

idM must remain VERSIO_HOSTED and avoid unavailable runtime dependencies.

## idM Decision

Design the MVP for PHP 8.3 and MariaDB 10.6 only.

## Remaining Risks

Production hardening and exact hosting configuration still need implementation validation.

## Recommendation

Proceed with a small PHP/MariaDB implementation after architecture closure.
