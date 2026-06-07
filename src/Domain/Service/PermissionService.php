<?php

declare(strict_types=1);

namespace IdM\Domain\Service;

use IdM\Audit\AuditLogger;
use IdM\Http\ApiException;
use IdM\Infrastructure\ActorContext;
use IdM\Infrastructure\Clock;
use IdM\Infrastructure\Database;
use IdM\Infrastructure\Uuid;
use IdM\Repository\PermissionRepository;

final class PermissionService
{
    public function __construct(
        private readonly Database $database,
        private readonly PermissionRepository $permissions,
        private readonly AuditLogger $audit,
        private readonly Clock $clock
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function create(array $payload, ActorContext $actor, string $correlationId): array
    {
        $permissionCode = trim((string) ($payload['permissionCode'] ?? ''));
        $permissionName = trim((string) ($payload['permissionName'] ?? ''));
        $description = isset($payload['description']) ? trim((string) $payload['description']) : null;

        if ($permissionCode === '' || $permissionName === '') {
            throw new ApiException('VALIDATION_ERROR', 'permissionCode and permissionName are required', 400);
        }
        if ($this->permissions->existsByCode($permissionCode)) {
            throw new ApiException('CONFLICT', 'permissionCode already exists', 409);
        }

        $permissionId = Uuid::v4();
        $now = $this->clock->nowUtc();
        $record = [
            'permissionId' => $permissionId,
            'permissionCode' => $permissionCode,
            'permissionName' => $permissionName,
            'description' => $description,
            'status' => 'ACTIVE',
            'createdAt' => $now,
            'updatedAt' => $now,
        ];

        $this->database->beginTransaction();
        try {
            $this->permissions->insert($record);
            $this->audit->log('Permission', $permissionId, 'CREATE_PERMISSION', $actor, $correlationId, ['permissionCode' => $permissionCode]);
            $this->database->commit();
        } catch (\Throwable $exception) {
            $this->database->rollBack();
            throw $exception;
        }

        return $record;
    }

    public function get(string $permissionId): array
    {
        $permission = $this->permissions->findById($permissionId);
        if ($permission === null) {
            throw new ApiException('NOT_FOUND', 'permission not found', 404);
        }

        return $permission;
    }

    /** @return list<array<string, mixed>> */
    public function list(): array
    {
        return $this->permissions->findAll();
    }

    /** @param array<string, mixed> $payload */
    public function update(string $permissionId, array $payload, ActorContext $actor, string $correlationId): array
    {
        $permission = $this->get($permissionId);
        if ((string) $permission['status'] === 'DISABLED') {
            throw new ApiException('VALIDATION_ERROR', 'disabled permission cannot be updated', 400);
        }

        $updates = [];
        $details = [];
        foreach (['permissionName', 'description'] as $field) {
            if (array_key_exists($field, $payload)) {
                $value = trim((string) $payload[$field]);
                if ($field === 'permissionName' && $value === '') {
                    throw new ApiException('VALIDATION_ERROR', 'permissionName cannot be empty', 400);
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
            $this->permissions->update($permissionId, $updates);
            $this->audit->log('Permission', $permissionId, 'UPDATE_PERMISSION', $actor, $correlationId, $details);
            $this->database->commit();
        } catch (\Throwable $exception) {
            $this->database->rollBack();
            throw $exception;
        }

        return $this->get($permissionId);
    }
}
