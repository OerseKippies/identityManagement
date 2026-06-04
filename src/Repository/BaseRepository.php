<?php

declare(strict_types=1);

namespace Idm\Repository;

use PDO;
use RuntimeException;

abstract class BaseRepository
{
    protected PDO $pdo;
    protected string $table;
    protected string $idColumn;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function list(): array
    {
        return $this->pdo->query("SELECT * FROM {$this->table}")->fetchAll();
    }

    public function find(string $id): array
    {
        $statement = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$this->idColumn} = :id");
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();
        if (!$row) {
            throw new RuntimeException('Resource not found', 404);
        }

        return $row;
    }

    public function insert(array $data): array
    {
        $columns = array_keys($data);
        $names = implode(', ', $columns);
        $params = ':' . implode(', :', $columns);
        $statement = $this->pdo->prepare("INSERT INTO {$this->table} ({$names}) VALUES ({$params})");
        $statement->execute($data);

        return $this->find($data[$this->idColumn]);
    }

    public function update(string $id, array $data): array
    {
        if ($data === []) {
            return $this->find($id);
        }

        $sets = implode(', ', array_map(static fn (string $column): string => "{$column} = :{$column}", array_keys($data)));
        $data['id'] = $id;
        $statement = $this->pdo->prepare("UPDATE {$this->table} SET {$sets} WHERE {$this->idColumn} = :id");
        $statement->execute($data);

        return $this->find($id);
    }

    public function existsBy(string $column, string $value): bool
    {
        $statement = $this->pdo->prepare("SELECT {$this->idColumn} FROM {$this->table} WHERE {$column} = :value LIMIT 1");
        $statement->execute(['value' => $value]);

        return (bool) $statement->fetch();
    }
}
