# RES-004: Service Account Patterns

Status: COMPLETE
Decision: ACCEPTED FOR MVP FOUNDATION

## Question

How should idM model non-human access subjects?

## Findings

Service accounts need lifecycle, least privilege, credential separation and auditability.

## OK-Core Impact

Service accounts must remain idM access subjects and must not become module ownership records.

## idM Decision

Model ServiceAccount with PENDING, ACTIVE, DISABLED and LOCKED states.

## Remaining Risks

Credential rotation and storage mechanics must be defined during implementation.

## Recommendation

Require purpose, least privilege and audit records for every service account.
