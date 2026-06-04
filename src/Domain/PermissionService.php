<?php

declare(strict_types=1);

namespace Idm\Domain;

use Idm\Repository\PermissionRepository;
use Idm\Support\Clock;
use Idm\Support\Uuid;
use Idm\Support\Validator;
use RuntimeException;

final class PermissionService
{
    private PermissionRepository $repository;
    private AuditService $audit;

    public function __construct(PermissionRepository $repository, AuditService $audit)
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
        Validator::uuid($id, 'permissionId');
        return $this->repository->find($id);
    }

    public function create(array $data): array
    {
        Validator::requireFields($data, ['permissionCode', 'permissionName']);
        if ($this->repository->existsBy('permissionCode', (string) $data['permissionCode'])) {
            throw new RuntimeException('Permission code already exists', 409);
        }
        $now = Clock::dbNow();
        $row = $this->repository->insert([
            'permissionId' => Uuid::v4(),
            'permissionCode' => (string) $data['permissionCode'],
            'permissionName' => (string) $data['permissionName'],
            'description' => $data['description'] ?? null,
            'status' => 'ACTIVE',
            'createdAt' => $now,
            'updatedAt' => $now,
        ]);
        $this->audit->record('Permission', $row['permissionId'], 'CREATE', $row);

        return $row;
    }

    public function assignToRole(string $roleId, string $permissionId): void
    {
        Validator::uuid($roleId, 'roleId');
        Validator::uuid($permissionId, 'permissionId');
        $this->repository->assignToRole($roleId, $permissionId, Uuid::v4(), Clock::dbNow());
        $this->audit->record('RolePermission', $roleId, 'ASSIGN_PERMISSION', ['permissionId' => $permissionId]);
    }

    public function removeFromRole(string $roleId, string $permissionId): void
    {
        Validator::uuid($roleId, 'roleId');
        Validator::uuid($permissionId, 'permissionId');
        $this->repository->removeFromRole($roleId, $permissionId);
        $this->audit->record('RolePermission', $roleId, 'REMOVE_PERMISSION', ['permissionId' => $permissionId]);
    }
}
