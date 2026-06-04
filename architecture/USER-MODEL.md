# User Model

Status: Architecture Foundation

## Purpose

User represents a human access subject known to idM.

User is not Customer, Contact, Employee, Supplier, or any other business-domain identity.

## Minimum Fields

| Field | Type | Notes |
|---|---|---|
| userId | UUID | idM-owned identifier |
| username | string | Login/display handle, unique within idM |
| displayName | string | Human-readable name |
| email | string | Access account email |
| status | enum | ACTIVE, DISABLED, LOCKED, PENDING |
| createdAt | datetime | Creation timestamp |
| updatedAt | datetime | Last update timestamp |

## Status Values

```text
ACTIVE
DISABLED
LOCKED
PENDING
```
