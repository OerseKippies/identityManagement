# idM MVP Implementation Roadmap

Status: Architecture Foundation

## Phase 1: Documentation Foundation

- Complete governance and architecture documentation.
- Confirm OK-Core boundary alignment.
- Publish local API draft foundation.

## Phase 2: Domain Models

- Define PHP model structures for User, Role, Permission, ServiceAccount, AccessPolicy, TokenReference.
- Define status values and validation rules.

## Phase 3: API Draft

- Refine OpenAPI draft.
- Submit draft to OK-Core for review.
- Align command/event names with communicationLayer.

## Phase 4: MariaDB Schema Design

- Create idM-owned schema design.
- Add migration scripts.
- Define indexes and constraints.

## Phase 5: PHP 8.3 Implementation

- Implement request handling.
- Implement persistence.
- Implement commL-compatible envelopes where required.

## Phase 6: Testing

- Add unit and integration tests appropriate for PHP baseline.
- Validate OpenAPI examples.
- Test boundary violations.

## Phase 7: Deployment

- Prepare Versio deployment notes.
- Configure HTTPS and database connection.
- Add cron jobs only where accepted.
