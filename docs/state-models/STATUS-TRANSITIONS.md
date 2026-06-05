# idM State Models

Status: MVP Runtime

Runtime enforcement: `src/Domain/StatusTransition.php`

## User / ServiceAccount

```text
PENDING -> ACTIVE -> DISABLED
              |
              v
            LOCKED -> ACTIVE
```

## Role / Permission

```text
ACTIVE <-> DISABLED (disable only for Role in MVP API)
```

## AccessPolicy

```text
DRAFT -> ACTIVE -> RETIRED
```

## TokenReference

```text
ACTIVE -> REVOKED
ACTIVE -> EXPIRED
```

## Architecture Reference

`architecture/STATUS-TRANSITIONS.md`
