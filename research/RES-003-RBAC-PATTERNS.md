# RES-003: RBAC Patterns

Status: COMPLETE
Decision: ACCEPTED FOR MVP FOUNDATION

## Question

What authorization model fits idM MVP?

## Findings

RBAC is sufficient for the MVP foundation:

```text
User -> Role
Role -> Permission
ServiceAccount -> Role
```

## OK-Core Impact

RBAC keeps authorization inside idM while avoiding foreign module workflow ownership.

## idM Decision

Use role and permission assignments as the MVP authorization model.

## Remaining Risks

Role hierarchy and permission naming conventions remain implementation design details.

## Recommendation

Start with flat RBAC; defer hierarchy until there is a proven need.
