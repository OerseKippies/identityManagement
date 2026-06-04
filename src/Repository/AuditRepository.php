<?php

declare(strict_types=1);

namespace Idm\Repository;

use PDO;

final class AuditRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(array $data): void
    {
        $statement = $this->pdo->prepare('INSERT INTO idm_audit_log (auditId, entityType, entityId, action, actorType, actorId, timestamp, detailsJson) VALUES (:auditId, :entityType, :entityId, :action, :actorType, :actorId, :timestamp, :detailsJson)');
        $statement->execute($data);
    }
}
