# Migration Evidence

Date: 2026-06-05
Command:

```text
D:\Programs\PHP\php.exe scripts\migrate.php
```

Result: PASS

First run output:

```text
Migration 001_initial_schema applied.
```

Idempotency re-run output:

```text
Migration 001_initial_schema already applied.
```

Database:

```text
Host: 185.175.200.60
Database: nol_module_idm
Migration file: migrations/001_initial_schema.sql
Registry table: idm_schema_migrations
```

Runtime fix applied during verification:

```text
scripts/migrate.php — prepare() moved inside PDOException handler for first-run when idm_schema_migrations does not exist yet
```

Assessment:

MariaDB migration executes successfully against configured `database.dbname`. Schema registry records migration `001_initial_schema`.
