# idM Testing

Status: MVP Implementation Foundation Complete

## Syntax Checks

Run PHP syntax checks over the source tree:

```bash
php -l public/index.php
```

Repeat for files in `src/` when making changes.

## Manual API Checks

Manual curl examples live in:

```text
tests/manual/
```

## Coverage Targets

- Ownership boundary tests
- API validation tests
- Status transition tests
- Permission assignment tests
- Audit creation tests
