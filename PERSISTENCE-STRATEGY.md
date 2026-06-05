# idM Persistence Strategy

Status: MVP Runtime

## Technology

```text
PHP 8.3 PDO
MariaDB 10.6
```

## Principles

- One module-owned database schema; no foreign module tables.
- UUID primary keys stored as `CHAR(36)`.
- UTC timestamps for `createdAt`, `updatedAt`, audit `timestamp`, and token lifecycle fields.
- Repositories encapsulate SQL; domain services own transactions and audit writes.

## Write Pattern

1. Begin transaction
2. Persist entity mutation
3. Write audit record with `correlationId`
4. Commit transaction

## Migration Strategy

- SQL migrations in `migrations/`
- Applied migrations recorded in `idm_schema_migrations`
- Apply with `php scripts/migrate.php`

## Runtime Components

```text
src/Repository/*
src/Domain/Service/*
migrations/001_initial_schema.sql
```
