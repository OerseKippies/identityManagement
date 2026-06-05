<?php

declare(strict_types=1);

namespace IdM\Repository;

use IdM\Infrastructure\Database;

final class TokenReferenceRepository
{
    public function __construct(private readonly Database $database)
    {
    }

    /** @return array<string, mixed>|null */
    public function findById(string $tokenReferenceId): ?array
    {
        $statement = $this->database->pdo()->prepare('SELECT * FROM idm_token_references WHERE tokenReferenceId = :tokenReferenceId LIMIT 1');
        $statement->execute(['tokenReferenceId' => $tokenReferenceId]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    /** @return list<array<string, mixed>> */
    public function findAll(): array
    {
        return $this->database->pdo()->query('SELECT * FROM idm_token_references ORDER BY issuedAt DESC')->fetchAll();
    }

    /** @param array<string, mixed> $data */
    public function insert(array $data): void
    {
        $sql = 'INSERT INTO idm_token_references
            (tokenReferenceId, subjectType, subjectId, issuedAt, expiresAt, revokedAt, status)
            VALUES
            (:tokenReferenceId, :subjectType, :subjectId, :issuedAt, :expiresAt, :revokedAt, :status)';
        $this->database->pdo()->prepare($sql)->execute($data);
    }

    /** @param array<string, mixed> $data */
    public function update(string $tokenReferenceId, array $data): void
    {
        $fields = [];
        $params = ['tokenReferenceId' => $tokenReferenceId];
        foreach ($data as $key => $value) {
            $fields[] = $key . ' = :' . $key;
            $params[$key] = $value;
        }

        $sql = 'UPDATE idm_token_references SET ' . implode(', ', $fields) . ' WHERE tokenReferenceId = :tokenReferenceId';
        $this->database->pdo()->prepare($sql)->execute($params);
    }
}
