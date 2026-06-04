<?php

declare(strict_types=1);

namespace Idm\Domain;

use Idm\Repository\UserRepository;
use Idm\Support\Clock;
use Idm\Support\Uuid;
use Idm\Support\Validator;
use RuntimeException;

final class UserService
{
    private UserRepository $repository;
    private AuditService $audit;

    public function __construct(UserRepository $repository, AuditService $audit)
    {
        $this->repository = $repository;
        $this->audit = $audit;
    }

    public function list(): array
    {
        return $this->repository->list();
    }

    public function get(string $id): array
    {
        Validator::uuid($id, 'userId');
        return $this->repository->find($id);
    }

    public function create(array $data): array
    {
        Validator::requireFields($data, ['username', 'displayName', 'email']);
        Validator::email((string) $data['email']);
        if ($this->repository->existsBy('username', (string) $data['username'])) {
            throw new RuntimeException('Username already exists', 409);
        }

        $now = Clock::dbNow();
        $row = $this->repository->insert([
            'userId' => Uuid::v4(),
            'username' => (string) $data['username'],
            'displayName' => (string) $data['displayName'],
            'email' => (string) $data['email'],
            'status' => $data['status'] ?? 'PENDING',
            'createdAt' => $now,
            'updatedAt' => $now,
        ]);
        $this->audit->record('User', $row['userId'], 'CREATE', $row);

        return $row;
    }

    public function update(string $id, array $data): array
    {
        $this->get($id);
        $update = [];
        foreach (['displayName', 'email'] as $field) {
            if (array_key_exists($field, $data)) {
                $update[$field] = (string) $data[$field];
            }
        }
        if (isset($update['email'])) {
            Validator::email($update['email']);
        }
        $update['updatedAt'] = Clock::dbNow();
        $row = $this->repository->update($id, $update);
        $this->audit->record('User', $id, 'UPDATE', $update);

        return $row;
    }

    public function transition(string $id, string $to, string $action): array
    {
        $current = $this->get($id);
        $from = $current['status'];
        $allowed = [
            'PENDING' => ['ACTIVE'],
            'ACTIVE' => ['DISABLED', 'LOCKED'],
            'LOCKED' => ['ACTIVE'],
            'DISABLED' => ['ACTIVE'],
        ];
        if (!in_array($to, $allowed[$from] ?? [], true)) {
            throw new RuntimeException("Invalid status transition {$from} -> {$to}", 409);
        }
        $row = $this->repository->update($id, ['status' => $to, 'updatedAt' => Clock::dbNow()]);
        $this->audit->record('User', $id, $action, ['from' => $from, 'to' => $to]);

        return $row;
    }
}
