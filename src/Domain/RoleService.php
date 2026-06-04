<?php

declare(strict_types=1);

namespace Idm\Domain;

use Idm\Repository\RoleRepository;
use Idm\Support\Clock;
use Idm\Support\Uuid;
use Idm\Support\Validator;
use RuntimeException;

final class RoleService
{
    private RoleRepository $repository;
    private AuditService $audit;

    public function __construct(RoleRepository $repository, AuditService $audit)
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
        Validator::uuid($id, 'roleId');
        return $this->repository->find($id);
    }

    public function create(array $data): array
    {
        Validator::requireFields($data, ['roleCode', 'roleName']);
        if ($this->repository->existsBy('roleCode', (string) $data['roleCode'])) {
            throw new RuntimeException('Role code already exists', 409);
        }
        $now = Clock::dbNow();
        $row = $this->repository->insert([
            'roleId' => Uuid::v4(),
            'roleCode' => (string) $data['roleCode'],
            'roleName' => (string) $data['roleName'],
            'description' => $data['description'] ?? null,
            'status' => 'ACTIVE',
            'createdAt' => $now,
            'updatedAt' => $now,
        ]);
        $this->audit->record('Role', $row['roleId'], 'CREATE', $row);

        return $row;
    }

    public function update(string $id, array $data): array
    {
        $this->get($id);
        $update = [];
        foreach (['roleName', 'description'] as $field) {
            if (array_key_exists($field, $data)) {
                $update[$field] = $data[$field];
            }
        }
        $update['updatedAt'] = Clock::dbNow();
        $row = $this->repository->update($id, $update);
        $this->audit->record('Role', $id, 'UPDATE', $update);

        return $row;
    }

    public function disable(string $id): array
    {
        $current = $this->get($id);
        if ($current['status'] !== 'ACTIVE') {
            throw new RuntimeException('Only active roles can be disabled', 409);
        }
        $row = $this->repository->update($id, ['status' => 'DISABLED', 'updatedAt' => Clock::dbNow()]);
        $this->audit->record('Role', $id, 'DISABLE');

        return $row;
    }

    public function assignToUser(string $userId, string $roleId): void
    {
        Validator::uuid($userId, 'userId');
        Validator::uuid($roleId, 'roleId');
        $this->repository->assignToUser($userId, $roleId, Uuid::v4(), Clock::dbNow());
        $this->audit->record('UserRole', $userId, 'ASSIGN_ROLE', ['roleId' => $roleId]);
    }

    public function removeFromUser(string $userId, string $roleId): void
    {
        Validator::uuid($userId, 'userId');
        Validator::uuid($roleId, 'roleId');
        $this->repository->removeFromUser($userId, $roleId);
        $this->audit->record('UserRole', $userId, 'REMOVE_ROLE', ['roleId' => $roleId]);
    }
}
