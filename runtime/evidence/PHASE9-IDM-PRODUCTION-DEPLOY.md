# PHASE9-IDM-PRODUCTION-DEPLOY

Date: 2026-06-07
Status: FAILED
Authority: OK-Core / PAEP Phase 9 — Production Readiness
Repository: OerseKippies/identityManagement
Target: https://idm.oerse-kippies.nl

## Mandatory Reading Performed

OK-Core onboarding and Phase 9 governance were checked before recording this evidence.

Read from `OerseKippies/OK-Core`:

- `START-HERE.md`
- `MANDATORY-READ-MATERIAL.md`
- `implementation/PHASE9-PRODUCTION-READINESS.md`
- `governance/decisions/GD-2026-06-06-DEPLOYMENT-TARGET-FIRST.md`
- `implementation/MVP-SCOPE.md`
- `architecture/MODULE-BOUNDARIES.md`
- `governance/STATUS-REPORTING-STANDARD.md`

## Requested Deployment

Command source: copM handover instruction via project chat.

```text
Repository: OerseKippies/identityManagement
Opdracht: PHASE9-VERSIO-DEPLOYMENT
Target: https://idm.oerse-kippies.nl
```

## Acceptance Criteria

| Criterion | Result | Evidence |
|---|---:|---|
| HTTPS active | FAIL | Target hostname did not resolve; HTTPS could not be reached. |
| Valid certificate | FAIL | Certificate could not be validated because hostname did not resolve. |
| `GET /health = 200` | FAIL | Endpoint could not be reached because hostname did not resolve. |
| commL reachable | FAIL | No production validation possible because idM target did not resolve. |
| actorContext endpoint reachable | FAIL | Endpoint could not be reached because hostname did not resolve. |
| users.list endpoint reachable | FAIL | Endpoint could not be reached because hostname did not resolve. |

## Runtime Check Evidence

Executed from the available validation environment:

```bash
for url in \
  https://idm.oerse-kippies.nl/ \
  https://idm.oerse-kippies.nl/health \
  https://idm.oerse-kippies.nl/actorContext \
  https://idm.oerse-kippies.nl/users.list; do
  echo "--- $url"
  curl -k -I -sS --max-time 10 "$url" | head -20 || true
done
```

Observed result:

```text
--- https://idm.oerse-kippies.nl/
curl: (6) Could not resolve host: idm.oerse-kippies.nl
--- https://idm.oerse-kippies.nl/health
curl: (6) Could not resolve host: idm.oerse-kippies.nl
--- https://idm.oerse-kippies.nl/actorContext
curl: (6) Could not resolve host: idm.oerse-kippies.nl
--- https://idm.oerse-kippies.nl/users.list
curl: (6) Could not resolve host: idm.oerse-kippies.nl
```

## Deployment Result

```text
Result: FAILED
Deployment: FAILED
Production URL: https://idm.oerse-kippies.nl
Commit: pending evidence commit
```

## Blocker

DNS for `idm.oerse-kippies.nl` is not resolving from the validation environment. Production deployment cannot be accepted until DNS resolves and all endpoints can be checked.

## Required Next Action

1. Configure DNS / hosting target for `idm.oerse-kippies.nl` on the approved Versio deployment target.
2. Deploy idM runtime to the configured production host.
3. Re-run acceptance checks:
   - HTTPS active
   - valid certificate
   - `GET /health = 200`
   - commL reachable
   - actorContext endpoint reachable
   - users.list endpoint reachable
4. Update or supersede this evidence file with PASS evidence and production commit reference.
