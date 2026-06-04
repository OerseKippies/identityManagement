# Access Policy Model

Status: Architecture Foundation

## Purpose

AccessPolicy represents an idM-owned access rule or policy definition.

The MVP policy model is intentionally small and does not define a complete policy engine.

## Minimum Fields

| Field | Type | Notes |
|---|---|---|
| policyId | UUID | idM-owned identifier |
| policyCode | string | Stable code |
| policyName | string | Human-readable name |
| description | string | Policy purpose |
| status | enum | DRAFT, ACTIVE, RETIRED |

## Policy Links

```text
Role -> Permission
User -> Role
ServiceAccount -> Role
```

## Boundary

Policies may reference access subjects and access grants.

Policies may not define foreign business-object identity ownership or lifecycle rules.
