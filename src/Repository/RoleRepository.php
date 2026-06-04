<?php

declare(strict_types=1);

namespace Idm\Repository;

use PDO;
use RuntimeException;

final class RoleRepository extends BaseRepository
{
    protected string $table = 'idm_roles';
    protected string $idColumn = 'roleId';

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    public function assignToUser(string $userId, string $roleId, string $userRoleId, string $assignedAt): void
    {
        $exists = $this->pdo->prepare('SELECT userRoleId FROM idm_user_roles WHERE userId = :userId AND roleId = :roleId');
        $exists->execute(['userId' => $userId, 'roleId' => $roleId]);
        if ($exists->fetch()) {
            throw new RuntimeException('Role is already assigned to user', 409);
        }

        $statement = $this->pdo->prepare('INSERT INTO idm_user_roles (userRoleId, userId, roleId, assignedAt, assignedByType, assignedById) VALUES (:userRoleId, :userId, :roleId, :assignedAt, :assignedByType, :assignedById)');
        $statement->execute([
            'userRoleId' => $userRoleId,
            'userId' => $userId,
            'roleId' => $roleId,
            'assignedAt' => $assignedAt,
            'assignedByType' => 'SYSTEM',
            'assignedById' => null,
        ]);
    }

    public function removeFromUser(string $userId, string $roleId): void
    {
        $statement = $this->pdo->prepare('DELETE FROM idm_user_roles WHERE userId = :userId AND roleId = :roleId');
        $statement->execute(['userId' => $userId, 'roleId' => $roleId]);
        if ($statement->rowCount() === 0) {
            throw new RuntimeException('Role assignment not found', 404);
        }
    }
}
