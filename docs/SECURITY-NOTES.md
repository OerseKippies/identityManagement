# idM Security Notes

Status: MVP Implementation Foundation Complete

## MVP Position

This MVP foundation provides access identity storage and administration endpoints.

It is not a complete IAM product and does not implement a full OAuth platform.

## TokenReference Rule

TokenReference stores metadata only.

Plain token secrets must not be stored.

## Audit

All state-changing endpoints write audit records with:

```text
actorType = SYSTEM
actorId = null
```

Future authenticated actors must remain idM-owned User or ServiceAccount subjects.

## Boundary

Security implementation must not introduce canonical identity, identity mapping, or foreign module ownership.
