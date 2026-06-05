<?php

declare(strict_types=1);

namespace IdM\Audit;

use IdM\Infrastructure\ActorContext;
use IdM\Infrastructure\Clock;

final class AuditLogger
{
    public function __construct(
        private readonly AuditRepository $repository,
        private readonly Clock $clock
    ) {
    }

    /** @param array<string, mixed>|null $details */
    public function log(
        string $entityType,
        string $entityId,
        string $action,
        ActorContext $actor,
        string $correlationId,
        ?array $details = null
    ): void {
        $this->repository->insert(
            $entityType,
            $entityId,
            $action,
            $actor->actorType,
            $actor->actorId,
            $correlationId,
            $this->clock->nowUtc(),
            $details
        );
    }
}
