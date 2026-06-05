<?php

declare(strict_types=1);

namespace IdM\Repository;

use IdM\Infrastructure\Database;

final class RoleRepository
{
    public function __construct(private readonly Database $database)
    {
    }

    /** @return array<string, mixed>|null */
    public function findById(string $roleId): ?array
    {
        $statement = $this->database->pdo()->prepare('SELECT * FROM idm_roles WHERE roleId = :roleId LIMIT 1');
        $statement->execute(['roleId' => $roleId]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    /** @return list<array<string, mixed>> */
    public function findAll(): array
    {
        return $this->database->pdo()->query('SELECT * FROM idm_roles ORDER BY createdAt DESC')->fetchAll();
    }

    public function existsByCode(string $roleCode): bool
    {
        $statement = $this->database->pdo()->prepare('SELECT 1 FROM idm_roles WHERE roleCode = :roleCode LIMIT 1');
        $statement->execute(['roleCode' => $roleCode]);

        return (bool) $statement->fetchColumn();
    }

    /** @param array<string, mixed> $data */
    public function insert(array $data): void
    {
        $sql = 'INSERT INTO idm_roles (roleId, roleCode, roleName, description, status, createdAt, updatedAt)
                VALUES (:roleId, :roleCode, :roleName, :description, :status, :createdAt, :updatedAt)';
        $this->database->pdo()->prepare($sql)->execute($data);
    }

    /** @param array<string, mixed> $data */
    public function update(string $roleId, array $data): void
    {
        $fields = [];
        $params = ['roleId' => $roleId];
        foreach ($data as $key => $value) {
            $fields[] = $key . ' = :' . $key;
            $params[$key] = $value;
        }

        $sql = 'UPDATE idm_roles SET ' . implode(', ', $fields) . ' WHERE roleId = :roleId';
        $this->database->pdo()->prepare($sql)->execute($params);
    }
}
