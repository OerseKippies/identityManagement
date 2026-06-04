# idM Status Transitions

Status: MVP Ready

## Purpose

This document defines allowed status transitions for idM-owned entities.

No transition may imply ownership of canonical business-object identity or foreign module lifecycle.

## User

| From | To | Meaning |
|---|---|---|
| PENDING | ACTIVE | User access is activated |
| ACTIVE | DISABLED | User access is disabled |
| ACTIVE | LOCKED | User access is locked |
| LOCKED | ACTIVE | User access is unlocked |
| DISABLED | ACTIVE | User access is re-enabled |

## Role

| From | To | Meaning |
|---|---|---|
| ACTIVE | DISABLED | Role can no longer be assigned or used |

## Permission

| From | To | Meaning |
|---|---|---|
| ACTIVE | DISABLED | Permission can no longer be assigned or used |

## ServiceAccount

| From | To | Meaning |
|---|---|---|
| PENDING | ACTIVE | Service account access is activated |
| ACTIVE | DISABLED | Service account access is disabled |
| ACTIVE | LOCKED | Service account access is locked |

## AccessPolicy

| From | To | Meaning |
|---|---|---|
| DRAFT | ACTIVE | Policy is active |
| ACTIVE | RETIRED | Policy is retired from active use |

## TokenReference

| From | To | Meaning |
|---|---|---|
| ACTIVE | REVOKED | Token reference is revoked |
| ACTIVE | EXPIRED | Token reference has expired |

## Validation Rule

Any status change outside this document must fail validation until accepted by OK-Core or a local idM ADR under OK-Core governance.
