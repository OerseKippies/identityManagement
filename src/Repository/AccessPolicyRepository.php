<?php

declare(strict_types=1);

namespace IdM\Repository;

use IdM\Infrastructure\Database;

final class AccessPolicyRepository
{
    public function __construct(private readonly Database $database)
    {
    }

    /** @return array<string, mixed>|null */
    public function findById(string $policyId): ?array
    {
        $statement = $this->database->pdo()->prepare('SELECT * FROM idm_access_policies WHERE policyId = :policyId LIMIT 1');
        $statement->execute(['policyId' => $policyId]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    /** @return list<array<string, mixed>> */
    public function findAll(): array
    {
        return $this->database->pdo()->query('SELECT * FROM idm_access_policies ORDER BY createdAt DESC')->fetchAll();
    }

    public function existsByCode(string $policyCode): bool
    {
        $statement = $this->database->pdo()->prepare('SELECT 1 FROM idm_access_policies WHERE policyCode = :policyCode LIMIT 1');
        $statement->execute(['policyCode' => $policyCode]);

        return (bool) $statement->fetchColumn();
    }

    /** @param array<string, mixed> $data */
    public function insert(array $data): void
    {
        $sql = 'INSERT INTO idm_access_policies (policyId, policyCode, policyName, description, status, createdAt, updatedAt)
                VALUES (:policyId, :policyCode, :policyName, :description, :status, :createdAt, :updatedAt)';
        $this->database->pdo()->prepare($sql)->execute($data);
    }

    /** @param array<string, mixed> $data */
    public function update(string $policyId, array $data): void
    {
        $fields = [];
        $params = ['policyId' => $policyId];
        foreach ($data as $key => $value) {
            $fields[] = $key . ' = :' . $key;
            $params[$key] = $value;
        }

        $sql = 'UPDATE idm_access_policies SET ' . implode(', ', $fields) . ' WHERE policyId = :policyId';
        $this->database->pdo()->prepare($sql)->execute($params);
    }
}
