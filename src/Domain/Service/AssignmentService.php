<?php

declare(strict_types=1);

namespace IdM\Domain\Service;

use IdM\Audit\AuditLogger;
use IdM\Http\ApiException;
use IdM\Infrastructure\ActorContext;
use IdM\Infrastructure\Clock;
use IdM\Infrastructure\Database;
use IdM\Repository\AssignmentRepository;
use IdM\Repository\PermissionRepository;
use IdM\Repository\RoleRepository;
use IdM\Repository\UserRepository;

final class AssignmentService
{
    public function __construct(
        private readonly Database $database,
        private readonly AssignmentRepository $assignments,
        private readonly UserRepository $users,
        private readonly RoleRepository $roles,
        private readonly PermissionRepository $permissions,
        private readonly AuditLogger $audit,
        private readonly Clock $clock
    ) {
    }

    public function assignRoleToUser(string $userId, string $roleId, ActorContext $actor, string $correlationId): void
    {
        if ($this->users->findById($userId) === null) {
            throw new ApiException('NOT_FOUND', 'user not found', 404);
        }
        if ($this->roles->findById($roleId) === null) {
            throw new ApiException('NOT_FOUND', 'role not found', 404);
        }
        if ($this->assignments->hasUserRole($userId, $roleId)) {
            throw new ApiException('CONFLICT', 'role already assigned to user', 409);
        }

        $this->database->beginTransaction();
        try {
            $this->assignments->assignRoleToUser(
                $userId,
                $roleId,
                $this->clock->nowUtc(),
                $actor->actorType,
                $actor->actorId
            );
            $this->audit->log('User', $userId, 'ASSIGN_ROLE', $actor, $correlationId, ['roleId' => $roleId]);
            $this->database->commit();
        } catch (\Throwable $exception) {
            $this->database->rollBack();
            throw $exception;
        }
    }

    public function removeRoleFromUser(string $userId, string $roleId, ActorContext $actor, string $correlationId): void
    {
        if ($this->users->findById($userId) === null) {
            throw new ApiException('NOT_FOUND', 'user not found', 404);
        }
        if ($this->roles->findById($roleId) === null) {
            throw new ApiException('NOT_FOUND', 'role not found', 404);
        }
        if (!$this->assignments->hasUserRole($userId, $roleId)) {
            throw new ApiException('NOT_FOUND', 'role assignment not found', 404);
        }

        $this->database->beginTransaction();
        try {
            $this->assignments->removeRoleFromUser($userId, $roleId);
            $this->audit->log('User', $userId, 'REMOVE_ROLE', $actor, $correlationId, ['roleId' => $roleId]);
            $this->database->commit();
        } catch (\Throwable $exception) {
            $this->database->rollBack();
            throw $exception;
        }
    }

    public function assignPermissionToRole(string $roleId, string $permissionId, ActorContext $actor, string $correlationId): void
    {
        if ($this->roles->findById($roleId) === null) {
            throw new ApiException('NOT_FOUND', 'role not found', 404);
        }
        if ($this->permissions->findById($permissionId) === null) {
            throw new ApiException('NOT_FOUND', 'permission not found', 404);
        }
        if ($this->assignments->hasRolePermission($roleId, $permissionId)) {
            throw new ApiException('CONFLICT', 'permission already assigned to role', 409);
        }

        $this->database->beginTransaction();
        try {
            $this->assignments->assignPermissionToRole(
                $roleId,
                $permissionId,
                $this->clock->nowUtc(),
                $actor->actorType,
                $actor->actorId
            );
            $this->audit->log('Role', $roleId, 'ASSIGN_PERMISSION', $actor, $correlationId, ['permissionId' => $permissionId]);
            $this->database->commit();
        } catch (\Throwable $exception) {
            $this->database->rollBack();
            throw $exception;
        }
    }

    public function removePermissionFromRole(string $roleId, string $permissionId, ActorContext $actor, string $correlationId): void
    {
        if ($this->roles->findById($roleId) === null) {
            throw new ApiException('NOT_FOUND', 'role not found', 404);
        }
        if ($this->permissions->findById($permissionId) === null) {
            throw new ApiException('NOT_FOUND', 'permission not found', 404);
        }
        if (!$this->assignments->hasRolePermission($roleId, $permissionId)) {
            throw new ApiException('NOT_FOUND', 'permission assignment not found', 404);
        }

        $this->database->beginTransaction();
        try {
            $this->assignments->removePermissionFromRole($roleId, $permissionId);
            $this->audit->log('Role', $roleId, 'REMOVE_PERMISSION', $actor, $correlationId, ['permissionId' => $permissionId]);
            $this->database->commit();
        } catch (\Throwable $exception) {
            $this->database->rollBack();
            throw $exception;
        }
    }
}
