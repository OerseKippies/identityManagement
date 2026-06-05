<?php

declare(strict_types=1);

namespace IdM\Repository;

use IdM\Infrastructure\Database;

final class ServiceAccountRepository
{
    public function __construct(private readonly Database $database)
    {
    }

    /** @return array<string, mixed>|null */
    public function findById(string $serviceAccountId): ?array
    {
        $statement = $this->database->pdo()->prepare('SELECT * FROM idm_service_accounts WHERE serviceAccountId = :serviceAccountId LIMIT 1');
        $statement->execute(['serviceAccountId' => $serviceAccountId]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    /** @return list<array<string, mixed>> */
    public function findAll(): array
    {
        return $this->database->pdo()->query('SELECT * FROM idm_service_accounts ORDER BY createdAt DESC')->fetchAll();
    }

    public function existsByName(string $accountName): bool
    {
        $statement = $this->database->pdo()->prepare('SELECT 1 FROM idm_service_accounts WHERE accountName = :accountName LIMIT 1');
        $statement->execute(['accountName' => $accountName]);

        return (bool) $statement->fetchColumn();
    }

    /** @param array<string, mixed> $data */
    public function insert(array $data): void
    {
        $sql = 'INSERT INTO idm_service_accounts (serviceAccountId, accountName, description, status, createdAt, updatedAt)
                VALUES (:serviceAccountId, :accountName, :description, :status, :createdAt, :updatedAt)';
        $this->database->pdo()->prepare($sql)->execute($data);
    }

    /** @param array<string, mixed> $data */
    public function update(string $serviceAccountId, array $data): void
    {
        $fields = [];
        $params = ['serviceAccountId' => $serviceAccountId];
        foreach ($data as $key => $value) {
            $fields[] = $key . ' = :' . $key;
            $params[$key] = $value;
        }

        $sql = 'UPDATE idm_service_accounts SET ' . implode(', ', $fields) . ' WHERE serviceAccountId = :serviceAccountId';
        $this->database->pdo()->prepare($sql)->execute($params);
    }
}
