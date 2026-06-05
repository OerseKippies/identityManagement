# idM Test Evidence

Date: 2026-06-05
Command:

```text
D:\Programs\PHP\php.exe tests\run.php
```

Result: PASS

Tests executed: 8
Tests passed: 8
Tests failed: 0

Output:

```text
[PASS] Uuid::v4 generates valid UUID v4
[PASS] Uuid::isValid rejects invalid values
[PASS] Correlation resolves supplied valid header
[PASS] Correlation generates UUID when header missing
[PASS] User PENDING to ACTIVE is allowed
[PASS] User DISABLED to LOCKED is rejected
[PASS] AccessPolicy DRAFT to ACTIVE is allowed
[PASS] Error response includes correlationId and errorCode
All unit tests passed.
```

PHP runtime:

```text
D:\Programs\PHP\php.exe
PHP 8.3.31
```

Covered areas:

| Area | Evidence |
|---|---|
| UUID generation | `tests/Unit/UuidTest.php` |
| CorrelationId handling | `tests/Unit/CorrelationTest.php` |
| Status transitions | `tests/Unit/StatusTransitionTest.php` |
| Error response shape | `tests/Unit/ErrorResponseTest.php` |

Verified separately in this pass:

```text
MariaDB migration execution — PASS (see MIGRATION-EVIDENCE.md)
Health endpoint success — PASS (see HEALTH-ENDPOINT-EVIDENCE.md)
```

Not executed in unit test command:

```text
CRUD endpoint execution
Audit row persistence
```
