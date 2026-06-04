# idM Status Transitions

Status: Architecture Foundation Complete

## Purpose

This document defines lifecycle states, valid transitions, invalid transitions and rationale for idM-owned entities.

No transition creates ownership of canonical business-object identity or foreign module lifecycle.

## User

Lifecycle states:

```text
PENDING
ACTIVE
DISABLED
LOCKED
```

Valid transitions:

| From | To | Rationale |
|---|---|---|
| PENDING | ACTIVE | Activate a newly created user |
| ACTIVE | DISABLED | Disable normal access |
| ACTIVE | LOCKED | Temporarily block access after risk signal |
| LOCKED | ACTIVE | Unlock after review |
| DISABLED | ACTIVE | Re-enable after administrative decision |

Invalid transition examples:

- DISABLED -> LOCKED is invalid.
- LOCKED -> DISABLED is invalid unless future governance explicitly allows it.
- PENDING -> LOCKED is invalid.

Diagram:

```text
PENDING -> ACTIVE -> DISABLED
              |
              v
            LOCKED
              |
              v
            ACTIVE
```

## ServiceAccount

Lifecycle states:

```text
PENDING
ACTIVE
DISABLED
LOCKED
```

Valid transitions:

| From | To | Rationale |
|---|---|---|
| PENDING | ACTIVE | Activate a reviewed service account |
| ACTIVE | DISABLED | Disable normal service access |
| ACTIVE | LOCKED | Temporarily block suspected compromise |
| LOCKED | ACTIVE | Unlock after review |
| DISABLED | ACTIVE | Re-enable after administrative decision |

Invalid transition examples:

- DISABLED -> LOCKED is invalid.
- PENDING -> DISABLED is invalid.
- LOCKED -> DISABLED is invalid unless future governance explicitly allows it.

Diagram:

```text
PENDING -> ACTIVE -> DISABLED
              |
              v
            LOCKED
              |
              v
            ACTIVE
```

## AccessPolicy

Lifecycle states:

```text
DRAFT
ACTIVE
RETIRED
```

Valid transitions:

| From | To | Rationale |
|---|---|---|
| DRAFT | ACTIVE | Publish policy for use |
| ACTIVE | RETIRED | Remove policy from active use |

Invalid transition examples:

- RETIRED -> ACTIVE is invalid unless future governance explicitly allows reinstatement.
- DRAFT -> RETIRED is invalid.

Diagram:

```text
DRAFT -> ACTIVE -> RETIRED
```

## TokenReference

Lifecycle states:

```text
ISSUED
ACTIVE
EXPIRED
REVOKED
```

Valid transitions:

| From | To | Rationale |
|---|---|---|
| ISSUED | ACTIVE | Token reference becomes usable |
| ACTIVE | EXPIRED | Token reference reaches expiration |
| ACTIVE | REVOKED | Token reference is administratively revoked |

Invalid transition examples:

- REVOKED -> ACTIVE is invalid.
- EXPIRED -> ACTIVE is invalid.
- ISSUED -> REVOKED is invalid unless future governance permits pre-activation revocation.

Diagram:

```text
ISSUED -> ACTIVE -> EXPIRED
             |
             v
          REVOKED
```
