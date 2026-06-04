# idM Test Strategy

Status: MVP Implementation Foundation Complete

## Purpose

The idM test strategy verifies that the implementation stays inside the accepted OK-Core boundary and that access identity behavior is predictable.

## Ownership Boundary Tests

- Reject or ignore fields that attempt to create CanonicalIdentity, IdentityRegistry, IdentityMapping, ExternalIdentifier, or foreign business-object identity.
- Verify idM stores no foreign module master records.
- Verify cross-module integration points are routed through communicationLayer (commL).

## API Validation Tests

- Validate required fields for User, Role, Permission, ServiceAccount, AccessPolicy, and TokenReference commands.
- Validate UUID formats.
- Validate duplicate codes and usernames return CONFLICT.
- Validate malformed payloads return VALIDATION_ERROR.

## Status Transition Tests

- Verify every transition in `architecture/STATUS-TRANSITIONS.md`.
- Reject unsupported transitions.
- Verify disabled or locked subjects cannot be used for active access decisions.

## Permission Assignment Tests

- Assign role to user.
- Remove role from user.
- Assign permission to role.
- Remove permission from role.
- Reject duplicate assignments.
- Reject assignments to missing, disabled, or invalid entities.

## Audit Creation Tests

- Verify audit records are created for create, update, disable, enable, lock, unlock, assignment, removal, issue-token, and revoke-token actions.
- Verify audit records include auditId, entityType, entityId, action, actorType, actorId, timestamp, and details.
- Verify audit details do not store foreign module records.

## Implementation Status

MVP IMPLEMENTATION FOUNDATION COMPLETE
