# idM Test Strategy (MVP Runtime)

Status: MVP Runtime

## Layers

1. Unit tests — correlation, UUID, status transitions, error model (`tests/Unit/*`)
2. Integration tests — MariaDB-backed API flows (manual or scripted against running PHP server)
3. Governance tests — ownership boundary checks in documentation and API surface

## Execution

```text
php tests/run.php
```

## Evidence Location

`docs/runtime-evidence/TEST-EVIDENCE.md`

Architecture reference: `architecture/TEST-STRATEGY.md`
