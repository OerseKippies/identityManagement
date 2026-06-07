<?php

declare(strict_types=1);

namespace IdM\Audit;

use IdM\Infrastructure\Database;
use IdM\Infrastructure\Uuid;

final class AuditRepository
{
    public function __construct(private readonly Database $database)
    {
    }

    /** @param array<string, mixed>|null $details */
    public function insert(
        string $entityType,
        string $entityId,
        string $action,
        string $actorType,
        ?string $actorId,
        string $correlationId,
        string $timestamp,
        ?array $details = null
    ): void {
        $sql = 'INSERT INTO idm_audit_log
            (auditId, entityType, entityId, action, actorType, actorId, correlationId, timestamp, detailsJson)
            VALUES
            (:auditId, :entityType, :entityId, :action, :actorType, :actorId, :correlationId, :timestamp, :detailsJson)';

        $statement = $this->database->pdo()->prepare($sql);
        $statement->execute([
            'auditId' => Uuid::v4(),
            'entityType' => $entityType,
            'entityId' => $entityId,
            'action' => $action,
            'actorType' => $actorType,
            'actorId' => $actorId,
            'correlationId' => $correlationId,
            'timestamp' => $timestamp,
            'detailsJson' => $details === null ? null : json_encode($details, JSON_THROW_ON_ERROR),
        ]);
    }

    public function countByCorrelationId(string $correlationId): int
    {
        $statement = $this->database->pdo()->prepare(
            'SELECT COUNT(*) AS total FROM idm_audit_log WHERE correlationId = :correlationId'
        );
        $statement->execute(['correlationId' => $correlationId]);
        $row = $statement->fetch();

        return (int) ($row['total'] ?? 0);
    }

    /** @return list<array<string, mixed>> */
    public function findByCorrelationId(string $correlationId): array
    {
        $statement = $this->database->pdo()->prepare(
            'SELECT auditId, entityType, entityId, action, actorType, actorId, correlationId, timestamp, detailsJson
             FROM idm_audit_log
             WHERE correlationId = :correlationId
             ORDER BY timestamp ASC'
        );
        $statement->execute(['correlationId' => $correlationId]);

        return $statement->fetchAll();
    }
}
