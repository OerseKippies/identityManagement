# idM Security Model

Status: MVP Runtime
Module: identityManagement (idM)

## Scope

idM secures access identity administration for idM-owned entities only:

```text
User
Role
Permission
ServiceAccount
AccessPolicy
TokenReference
```

## Authentication (MVP)

- API access requires `X-Api-Key` when `api.require_api_key` is enabled in configuration.
- Health endpoint (`GET /v1/health`) is unauthenticated for operational checks.
- Human and service account credential storage remains outside source control.

## Authorization (MVP)

- RBAC model: User/ServiceAccount -> Role -> Permission.
- AccessPolicy governs idM access behavior only.
- Actor context is supplied through `X-Actor-Type` and `X-Actor-Id` headers for audit attribution.

## Password And Token Rules

- Password hashes use PHP `password_hash` when credential storage is introduced.
- TokenReference stores metadata only; plain token secrets are not persisted.

## Boundary

idM does not implement CanonicalIdentity, identity registry, identity mapping, or cross-domain identity resolution.

## Runtime Evidence

- `src/Application.php` — API key validation
- `docs/runtime-evidence/API-EXAMPLES.md` — authorized and unauthorized request examples

## Architecture Reference

Detailed assumptions: `architecture/SECURITY-MODEL.md`
