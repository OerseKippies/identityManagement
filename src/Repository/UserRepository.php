<?php

declare(strict_types=1);

namespace IdM\Repository;

use IdM\Infrastructure\Database;

final class UserRepository
{
    public function __construct(private readonly Database $database)
    {
    }

    /** @return array<string, mixed>|null */
    public function findById(string $userId): ?array
    {
        $statement = $this->database->pdo()->prepare('SELECT * FROM idm_users WHERE userId = :userId LIMIT 1');
        $statement->execute(['userId' => $userId]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    /** @return list<array<string, mixed>> */
    public function findAll(): array
    {
        $statement = $this->database->pdo()->query('SELECT * FROM idm_users ORDER BY createdAt DESC');

        return $statement->fetchAll();
    }

    public function existsByUsername(string $username): bool
    {
        $statement = $this->database->pdo()->prepare('SELECT 1 FROM idm_users WHERE username = :username LIMIT 1');
        $statement->execute(['username' => $username]);

        return (bool) $statement->fetchColumn();
    }

    public function existsByEmail(string $email): bool
    {
        $statement = $this->database->pdo()->prepare('SELECT 1 FROM idm_users WHERE email = :email LIMIT 1');
        $statement->execute(['email' => $email]);

        return (bool) $statement->fetchColumn();
    }

    /** @param array<string, mixed> $data */
    public function insert(array $data): void
    {
        $sql = 'INSERT INTO idm_users (userId, username, displayName, email, status, createdAt, updatedAt)
                VALUES (:userId, :username, :displayName, :email, :status, :createdAt, :updatedAt)';
        $statement = $this->database->pdo()->prepare($sql);
        $statement->execute($data);
    }

    /** @param array<string, mixed> $data */
    public function update(string $userId, array $data): void
    {
        $fields = [];
        $params = ['userId' => $userId];
        foreach ($data as $key => $value) {
            $fields[] = $key . ' = :' . $key;
            $params[$key] = $value;
        }

        $sql = 'UPDATE idm_users SET ' . implode(', ', $fields) . ' WHERE userId = :userId';
        $statement = $this->database->pdo()->prepare($sql);
        $statement->execute($params);
    }
}
