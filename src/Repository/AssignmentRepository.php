<?php

declare(strict_types=1);

namespace IdM\Repository;

use IdM\Infrastructure\Database;
use IdM\Infrastructure\Uuid;

final class AssignmentRepository
{
    public function __construct(private readonly Database $database)
    {
    }

    public function assignRoleToUser(
        string $userId,
        string $roleId,
        string $assignedAt,
        string $assignedByType,
        ?string $assignedById
    ): void {
        $sql = 'INSERT INTO idm_user_roles (userRoleId, userId, roleId, assignedAt, assignedByType, assignedById)
                VALUES (:userRoleId, :userId, :roleId, :assignedAt, :assignedByType, :assignedById)';
        $this->database->pdo()->prepare($sql)->execute([
            'userRoleId' => Uuid::v4(),
            'userId' => $userId,
            'roleId' => $roleId,
            'assignedAt' => $assignedAt,
            'assignedByType' => $assignedByType,
            'assignedById' => $assignedById,
        ]);
    }

    public function removeRoleFromUser(string $userId, string $roleId): bool
    {
        $statement = $this->database->pdo()->prepare('DELETE FROM idm_user_roles WHERE userId = :userId AND roleId = :roleId');
        $statement->execute(['userId' => $userId, 'roleId' => $roleId]);

        return $statement->rowCount() > 0;
    }

    public function hasUserRole(string $userId, string $roleId): bool
    {
        $statement = $this->database->pdo()->prepare('SELECT 1 FROM idm_user_roles WHERE userId = :userId AND roleId = :roleId LIMIT 1');
        $statement->execute(['userId' => $userId, 'roleId' => $roleId]);

        return (bool) $statement->fetchColumn();
    }

    public function assignPermissionToRole(
        string $roleId,
        string $permissionId,
        string $assignedAt,
        string $assignedByType,
        ?string $assignedById
    ): void {
        $sql = 'INSERT INTO idm_role_permissions (rolePermissionId, roleId, permissionId, assignedAt, assignedByType, assignedById)
                VALUES (:rolePermissionId, :roleId, :permissionId, :assignedAt, :assignedByType, :assignedById)';
        $this->database->pdo()->prepare($sql)->execute([
            'rolePermissionId' => Uuid::v4(),
            'roleId' => $roleId,
            'permissionId' => $permissionId,
            'assignedAt' => $assignedAt,
            'assignedByType' => $assignedByType,
            'assignedById' => $assignedById,
        ]);
    }

    public function removePermissionFromRole(string $roleId, string $permissionId): bool
    {
        $statement = $this->database->pdo()->prepare(
            'DELETE FROM idm_role_permissions WHERE roleId = :roleId AND permissionId = :permissionId'
        );
        $statement->execute(['roleId' => $roleId, 'permissionId' => $permissionId]);

        return $statement->rowCount() > 0;
    }

    public function hasRolePermission(string $roleId, string $permissionId): bool
    {
        $statement = $this->database->pdo()->prepare(
            'SELECT 1 FROM idm_role_permissions WHERE roleId = :roleId AND permissionId = :permissionId LIMIT 1'
        );
        $statement->execute(['roleId' => $roleId, 'permissionId' => $permissionId]);

        return (bool) $statement->fetchColumn();
    }
}
