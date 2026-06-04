# idM Security Model

Status: Architecture Foundation Complete

## 1. Authentication Strategy

Human user authentication is assumed to be implemented in the future MVP implementation using idM-owned User records.

Service account authentication is assumed to use idM-owned ServiceAccount records and credentials managed outside source control.

Local idM scope:

```text
User
ServiceAccount
TokenReference
Role
Permission
AccessPolicy
```

commL integration assumption:

```text
External module -> communicationLayer (commL) -> idM
```

idM does not become a full OAuth/OIDC provider in the MVP.

## 2. Authorization Strategy

The MVP authorization model is RBAC-centered:

```text
User -> Role
Role -> Permission
ServiceAccount -> Role
AccessPolicy -> access policy definition
```

AccessPolicy may describe access behavior for idM-owned access subjects and grants.

AccessPolicy may not control foreign business lifecycle or foreign domain ownership.

## 3. Password Policy

Minimum length:

```text
12 characters recommended for MVP implementation
```

Complexity assumptions:

```text
mixed case, number or symbol recommended
common password rejection recommended
```

Password hashing expectations:

```text
Use PHP password_hash with a current strong algorithm.
Never store plain-text passwords.
Never store reversible passwords.
```

Reset expectations:

```text
Reset tokens must be time-limited.
Reset secrets must not be stored in plain text.
```

Storage rules:

```text
Credential secrets stay outside source control.
Password hashes belong only in the future idM-owned schema.
```

Future implementation constraints:

```text
Must run on PHP 8.3 and MariaDB 10.6.
Must not require NodeJS, Redis, RabbitMQ, Docker, WebSockets, Python 3, or long-running daemons.
```

## 4. Service Account Policy

Creation rules:

- Service accounts must have a documented purpose.
- Service accounts should start as PENDING until activated.
- Names must be unique.

Lifecycle:

```text
PENDING -> ACTIVE
ACTIVE -> DISABLED
ACTIVE -> LOCKED
LOCKED -> ACTIVE
DISABLED -> ACTIVE
```

Lock/disable rules:

- Lock for suspected credential compromise.
- Disable when no longer needed.

Credential assumptions:

- Credentials are not committed.
- Credentials are rotated after suspected exposure.

Least privilege:

- Assign only required roles.
- Review role assignments before production use.

Audit requirements:

- Creation, update, disable, enable, lock and unlock must be audited.

## 5. Token Strategy

idM owns TokenReference only.

TokenReference stores metadata and lifecycle state, not plain-text token secrets.

Expiration:

- Every token reference must have an expiration timestamp.

Revocation:

- Revocation must set revokedAt and status.

Retention:

- Expired and revoked references may be retained for audit for an accepted retention period.

Audit expectations:

- Create, revoke and expire operations must be audited.

## 6. Access Policy Model

AccessPolicy relates to idM access rules and role/permission decisions.

MVP limitations:

- No full policy engine.
- No external business lifecycle control.
- No cross-domain identity mapping.

## 7. Audit Requirements

All state-changing operations must be audited.

Actor assumptions:

```text
USER
SERVICE_ACCOUNT
SYSTEM
```

Retention assumptions:

- Retention period is open for future governance.
- Audit records should not be silently deleted before retention is defined.

Tamper-evidence expectations:

- MVP design should preserve append-only behavior where practical.
- Post-MVP may add hash chaining or export controls if required.

## 8. Security Assumptions

- HTTPS is required.
- No shared secrets in source control.
- No direct database access from foreign modules.
- Least privilege applies to users, service accounts and database credentials.
- Config should live outside webroot where possible.
- MariaDB credentials must not be committed.
- Future production hardening is required before live use.
