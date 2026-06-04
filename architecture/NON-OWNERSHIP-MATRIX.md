# idM Non-Ownership Matrix

Status: Architecture Foundation

idM explicitly does not own:

| Concept | Owner / Status | idM Rule |
|---|---|---|
| CanonicalIdentity | Deferred by OK-Core | Do not implement |
| IdentityRegistry | Deferred by OK-Core | Do not implement |
| IdentityMapping | Deferred by OK-Core | Do not implement |
| ExternalIdentifier | Deferred by OK-Core | Do not implement |
| IdentityResolution | Deferred by OK-Core | Do not implement |
| IdentityLifecycle | Deferred by OK-Core | Do not implement |
| IdentityAuditRecord | Deferred by OK-Core | Do not implement |
| AnimalId | Foreign domain | Reference only if approved |
| ProductId | Foreign domain | Reference only if approved |
| BreedId | Foreign domain | Reference only if approved |
| CustomerId | Foreign domain | Reference only if approved |
| ContactId | relationshipManagement | Reference only if approved |
| PublicationId | publicationManagement | Reference only if approved |
| AdvertisementId | adManagement | Reference only if approved |
| InventoryItemId | inventoryManagement | Reference only if approved |
| HatchRunId | hatcheryManagement | Reference only if approved |
| OrderId | salesManagement | Reference only if approved |

## Guardrail

Allowed references such as `foreignModuleObjectId`, `externalReferenceId`, `snapshotForAudit`, and `cachedReadModel` must never become source-of-truth ownership.
