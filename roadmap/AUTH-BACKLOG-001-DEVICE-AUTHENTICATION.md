# AUTH-BACKLOG-001 Device Based Authentication

| Field | Value |
|---|---|
| **Title** | AUTH-BACKLOG-001 Device Based Authentication |
| **Status** | BACKLOG |
| **Priority** | MEDIUM |
| **Classification** | Future Enhancement |
| **Repository** | OerseKippies/identityManagement |
| **Authority** | OerseKippies/OK-Core/START-HERE.md |
| **Date registered** | 2026-06-07 |

## Approval Status

```text
BACKLOG REGISTERED
```

## Review Status

```text
PASS
```

---

## Summary

Register a future authentication capability where trusted devices authenticate users without manual username/password entry after initial device registration. idM remains the authority for device trust validation; copM and commL participate in the mediation chain.

**This item is documentation only. No implementation is in scope.**

---

## Not Required For

| Gate | Required? |
|---|---|
| PAEP completion | No |
| MVP | No |
| Current runtime approval | No |

---

## Future Scope

- Passkeys (WebAuthn)
- Windows Hello
- Android biometric authentication
- Trusted devices
- Device registration
- Device revocation
- Device trust lifecycle
- No username/password required after registration

---

## Target Flow

```text
Device
  → copM (presentation + device credential)
  → commL (contract enforcement, routing)
  → idM (validates trusted device, resolves actor context)
  ↓
Authenticated (no manual login)
```

### Flow detail (draft)

1. **Registration (one-time):** User completes initial proof-of-identity (out of scope for this backlog item definition). Device public key / WebAuthn credential is registered in idM-owned device trust store.
2. **Subsequent access:** Device presents device-bound credential to copM.
3. **Mediation:** copM invokes commL contract (TBD) with device attestation payload.
4. **Validation:** idM verifies device is registered, not revoked, and within trust lifecycle policy.
5. **Resolution:** idM returns actor context; copM establishes presentation session without user picker or password.

---

## Ownership Boundaries

| Module | Responsibility |
|---|---|
| **idM** | Device trust registry, validation, revocation, actor context authority |
| **commL** | Contract enforcement, consumer authorization, routing |
| **copM** | Device credential capture, presentation session only |

idM must not delegate device trust truth to copM or foreign modules.

---

## Dependencies (Future)

- WebAuthn / FIDO2 server-side validation infrastructure (PHP-compatible)
- idM schema extension for device registrations and trust lifecycle
- commL contract definitions for device authentication
- copM UI for registration and silent re-authentication
- Security model update (`architecture/SECURITY-MODEL.md`)

---

## Open Questions (Deferred)

- Credential type enum extension (`DEVICE` vs `PASSKEY` vs `BIOMETRIC`)
- Cross-device sync and recovery policy
- Revocation propagation latency requirements
- Audit event model for device trust changes

---

## References

- `runtime/evidence/IDM-CURRENT-LOGIN-METHOD.md` — current login baseline
- `contracts/COPM-IDM-AUTH-CONTRACT.md` — existing auth contract pattern
- `architecture/SECURITY-MODEL.md` — authentication strategy
- `roadmap/MVP-BACKLOG.md` — MVP scope closure

---

## GitHub Evidence

| Field | Value |
|---|---|
| File | `roadmap/AUTH-BACKLOG-001-DEVICE-AUTHENTICATION.md` |
| Commit | `0c5357d` |
| Repository | https://github.com/OerseKippies/identityManagement |
