# identityManagement Module Scope

Status: Architecture Foundation Complete

## Owned

idM owns only:

- User
- Role
- Permission
- ServiceAccount
- AccessPolicy
- TokenReference

## Not Owned

idM does not own:

- canonical identity concepts
- business-object identity concepts
- foreign module data
- foreign module workflows
- cross-domain identity mapping
- canonical identifiers for animals, products, customers, contacts, orders, publications, advertisements, inventory items, or hatch runs

## Boundary Rule

idM may not become a hidden owner of foreign data through local copies, read models, audit payloads, or access policy definitions.
