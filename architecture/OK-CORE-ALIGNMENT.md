# idM OK-Core Alignment

Status: Architecture Foundation

## Alignment Points

| OK-Core Rule | idM Alignment |
|---|---|
| communicationLayer mandatory boundary | All cross-module contracts route through commL |
| One module, one ownership boundary | idM owns only access identity |
| One database/schema per module | idM database contains only idM-owned data |
| API drafts start in module | `docs/api/idm-api-draft.yaml` is local draft |
| Canonical APIs belong in OK-Core | idM draft status is DRAFT_IN_MODULE |
| Versio-first strategy | idM classification is VERSIO_HOSTED |

## Explicit Non-Ownership

idM does not introduce CanonicalIdentity architecture.

idM does not introduce cross-domain identity ownership.

idM does not own canonical business-object identifiers.
