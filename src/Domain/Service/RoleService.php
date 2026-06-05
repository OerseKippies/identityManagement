<?php

declare(strict_types=1);

namespace IdM\Domain\Service;

use IdM\Audit\AuditLogger;
use IdM\Http\ApiException;
use IdM\Infrastructure\ActorContext;
use IdM\Infrastructure\Clock;
use IdM\Infrastructure\Database;
use IdM\Infrastructure\Uuid;
use IdM\Repository\RoleRepository;

final class RoleService
{
    public function __construct(
        private readonly Database $database,
        private readonly RoleRepository $roles,
        private readonly AuditLogger $audit,
        private readonly Clock $clock
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function create(array $payload, ActorContext $actor, string $correlationId): array
    {
        $roleCode = trim((string) ($payload['roleCode'] ?? ''));
        $roleName = trim((string) ($payload['roleName'] ?? ''));
        $description = isset($payload['description']) ? trim((string) $payload['description']) : null;

        if ($roleCode === '' || $roleName === '') {
            throw new ApiException('VALIDATION_ERROR', 'roleCode and roleName are required', 400);
        }
        if ($this->roles->existsByCode($roleCode)) {
            throw new ApiException('CONFLICT', 'roleCode already exists', 409);
        }

        $roleId = Uuid::v4();
        $now = $this->clock->nowUtc();
        $record = [
            'roleId' => $roleId,
            'roleCode' => $roleCode,
            'roleName' => $roleName,
            'description' => $description,
            'status' => 'ACTIVE',
            'createdAt' => $now,
            'updatedAt' => $now,
        ];

        $this->database->beginTransaction();
        try {
            $this->roles->insert($record);
            $this->audit->log('Role', $roleId, 'CREATE_ROLE', $actor, $correlationId, ['roleCode' => $roleCode]);
            $this->database->commit();
        } catch (\Throwable $exception) {
            $this->database->rollBack();
            throw $exception;
        }

        return $record;
    }

    public function get(string $roleId): array
    {
        $role = $this->roles->findById($roleId);
        if ($role === null) {
            throw new ApiException('NOT_FOUND', 'role not found', 404);
        }

        return $role;
    }

    /** @return list<array<string, mixed>> */
    public function list(): array
    {
        return $this->roles->findAll();
    }

    /** @param array<string, mixed> $payload */
    public function update(string $roleId, array $payload, ActorContext $actor, string $correlationId): array
    {
        $role = $this->get($roleId);
        if ((string) $role['status'] === 'DISABLED') {
            throw new ApiException('VALIDATION_ERROR', 'disabled role cannot be updated', 400);
        }

        $updates = [];
        $details = [];
        foreach (['roleName', 'description'] as $field) {
            if (array_key_exists($field, $payload)) {
                $value = trim((string) $payload[$field]);
                if ($field === 'roleName' && $value === '') {
                    throw new ApiException('VALIDATION_ERROR', 'roleName cannot be empty', 400);
                }
                $updates[$field] = $value === '' ? null : $value;
                $details[$field] = $updates[$field];
            }
        }

        if ($updates === []) {
            throw new ApiException('VALIDATION_ERROR', 'no updatable fields provided', 400);
        }

        $updates['updatedAt'] = $this->clock->nowUtc();

        $this->database->beginTransaction();
        try {
            $this->roles->update($roleId, $updates);
            $this->audit->log('Role', $roleId, 'UPDATE_ROLE', $actor, $correlationId, $details);
            $this->database->commit();
        } catch (\Throwable $exception) {
            $this->database->rollBack();
            throw $exception;
        }

        return $this->get($roleId);
    }

    public function disable(string $roleId, ActorContext $actor, string $correlationId): array
    {
        $role = $this->get($roleId);
        if ((string) $role['status'] === 'DISABLED') {
            return $role;
        }

        $this->database->beginTransaction();
        try {
            $this->roles->update($roleId, ['status' => 'DISABLED', 'updatedAt' => $this->clock->nowUtc()]);
            $this->audit->log('Role', $roleId, 'DISABLE_ROLE', $actor, $correlationId);
            $this->database->commit();
        } catch (\Throwable $exception) {
            $this->database->rollBack();
            throw $exception;
        }

        return $this->get($roleId);
    }
}
