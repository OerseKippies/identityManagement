<?php

declare(strict_types=1);

namespace Idm\Domain;

use Idm\Repository\AuditRepository;
use Idm\Support\Clock;
use Idm\Support\Uuid;

final class AuditService
{
    private AuditRepository $repository;

    public function __construct(AuditRepository $repository)
    {
        $this->repository = $repository;
    }

    public function record(string $entityType, string $entityId, string $action, array $details = []): void
    {
        $this->repository->insert([
            'auditId' => Uuid::v4(),
            'entityType' => $entityType,
            'entityId' => $entityId,
            'action' => $action,
            'actorType' => 'SYSTEM',
            'actorId' => null,
            'timestamp' => Clock::dbNow(),
            'detailsJson' => json_encode($details, JSON_UNESCAPED_SLASHES),
        ]);
    }
}
