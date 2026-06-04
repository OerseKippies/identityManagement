# idM Ownership Matrix

Status: Architecture Foundation

| Concept | Owned by idM | Notes |
|---|---:|---|
| User | Yes | Access user account, not a customer/contact record |
| Role | Yes | Access grouping |
| Permission | Yes | Capability string or action grant |
| ServiceAccount | Yes | Non-human access subject |
| AccessPolicy | Yes | idM policy definition for access behavior |
| TokenReference | Yes | Token metadata and reference only |

## Ownership Rule

idM may store data required to authenticate, authorize, disable, lock, or reason about access subjects.

idM may not infer ownership of any foreign business object from an access decision.
