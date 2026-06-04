<?php

declare(strict_types=1);

namespace Idm\Repository;

use PDO;
use RuntimeException;

final class PermissionRepository extends BaseRepository
{
    protected string $table = 'idm_permissions';
    protected string $idColumn = 'permissionId';

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    public function assignToRole(string $roleId, string $permissionId, string $rolePermissionId, string $assignedAt): void
    {
        $exists = $this->pdo->prepare('SELECT rolePermissionId FROM idm_role_permissions WHERE roleId = :roleId AND permissionId = :permissionId');
        $exists->execute(['roleId' => $roleId, 'permissionId' => $permissionId]);
        if ($exists->fetch()) {
            throw new RuntimeException('Permission is already assigned to role', 409);
        }

        $statement = $this->pdo->prepare('INSERT INTO idm_role_permissions (rolePermissionId, roleId, permissionId, assignedAt, assignedByType, assignedById) VALUES (:rolePermissionId, :roleId, :permissionId, :assignedAt, :assignedByType, :assignedById)');
        $statement->execute([
            'rolePermissionId' => $rolePermissionId,
            'roleId' => $roleId,
            'permissionId' => $permissionId,
            'assignedAt' => $assignedAt,
            'assignedByType' => 'SYSTEM',
            'assignedById' => null,
        ]);
    }

    public function removeFromRole(string $roleId, string $permissionId): void
    {
        $statement = $this->pdo->prepare('DELETE FROM idm_role_permissions WHERE roleId = :roleId AND permissionId = :permissionId');
        $statement->execute(['roleId' => $roleId, 'permissionId' => $permissionId]);
        if ($statement->rowCount() === 0) {
            throw new RuntimeException('Permission assignment not found', 404);
        }
    }
}
