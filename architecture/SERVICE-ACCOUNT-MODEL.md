# Service Account Model

Status: Architecture Foundation

## Purpose

ServiceAccount represents a non-human access subject used by a system, module, integration, or scheduled process.

ServiceAccount is not a foreign module identity registry.

## Minimum Fields

| Field | Type | Notes |
|---|---|---|
| serviceAccountId | UUID | idM-owned identifier |
| accountName | string | Stable account name |
| description | string | Purpose and expected consumer |
| status | enum | ACTIVE, DISABLED, LOCKED, PENDING |
| createdAt | datetime | Creation timestamp |

## Status Values

```text
ACTIVE
DISABLED
LOCKED
PENDING
```
