# Role Permission Model

Status: Architecture Foundation

## Role

Role represents an idM-owned access grouping.

Minimum fields:

| Field | Type | Notes |
|---|---|---|
| roleId | UUID | idM-owned identifier |
| roleCode | string | Stable code, for example administrator |
| roleName | string | Human-readable name |
| description | string | Role purpose |
| status | enum | ACTIVE, DISABLED |

Examples:

```text
Administrator
Operator
Viewer
Service
```

## Permission

Permission represents an idM-owned capability grant.

Minimum fields:

| Field | Type | Notes |
|---|---|---|
| permissionId | UUID | idM-owned identifier |
| permissionCode | string | Stable code |
| permissionName | string | Human-readable name |
| description | string | Permission purpose |

Examples:

```text
user.read
user.write
role.read
role.write
policy.read
policy.write
```

## Links

```text
Role -> Permission
User -> Role
ServiceAccount -> Role
```
