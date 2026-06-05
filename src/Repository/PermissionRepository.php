<?php

declare(strict_types=1);

namespace IdM\Repository;

use IdM\Infrastructure\Database;

final class PermissionRepository
{
    public function __construct(private readonly Database $database)
    {
    }

    /** @return array<string, mixed>|null */
    public function findById(string $permissionId): ?array
    {
        $statement = $this->database->pdo()->prepare('SELECT * FROM idm_permissions WHERE permissionId = :permissionId LIMIT 1');
        $statement->execute(['permissionId' => $permissionId]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    /** @return list<array<string, mixed>> */
    public function findAll(): array
    {
        return $this->database->pdo()->query('SELECT * FROM idm_permissions ORDER BY createdAt DESC')->fetchAll();
    }

    public function existsByCode(string $permissionCode): bool
    {
        $statement = $this->database->pdo()->prepare('SELECT 1 FROM idm_permissions WHERE permissionCode = :permissionCode LIMIT 1');
        $statement->execute(['permissionCode' => $permissionCode]);

        return (bool) $statement->fetchColumn();
    }

    /** @param array<string, mixed> $data */
    public function insert(array $data): void
    {
        $sql = 'INSERT INTO idm_permissions (permissionId, permissionCode, permissionName, description, status, createdAt, updatedAt)
                VALUES (:permissionId, :permissionCode, :permissionName, :description, :status, :createdAt, :updatedAt)';
        $this->database->pdo()->prepare($sql)->execute($data);
    }
}
