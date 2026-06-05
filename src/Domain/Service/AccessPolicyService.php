<?php

declare(strict_types=1);

namespace IdM\Domain\Service;

use IdM\Audit\AuditLogger;
use IdM\Domain\StatusTransition;
use IdM\Http\ApiException;
use IdM\Infrastructure\ActorContext;
use IdM\Infrastructure\Clock;
use IdM\Infrastructure\Database;
use IdM\Infrastructure\Uuid;
use IdM\Repository\AccessPolicyRepository;

final class AccessPolicyService
{
    public function __construct(
        private readonly Database $database,
        private readonly AccessPolicyRepository $policies,
        private readonly AuditLogger $audit,
        private readonly Clock $clock
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function create(array $payload, ActorContext $actor, string $correlationId): array
    {
        $policyCode = trim((string) ($payload['policyCode'] ?? ''));
        $policyName = trim((string) ($payload['policyName'] ?? ''));
        $description = isset($payload['description']) ? trim((string) $payload['description']) : null;

        if ($policyCode === '' || $policyName === '') {
            throw new ApiException('VALIDATION_ERROR', 'policyCode and policyName are required', 400);
        }
        if ($this->policies->existsByCode($policyCode)) {
            throw new ApiException('CONFLICT', 'policyCode already exists', 409);
        }

        $policyId = Uuid::v4();
        $now = $this->clock->nowUtc();
        $record = [
            'policyId' => $policyId,
            'policyCode' => $policyCode,
            'policyName' => $policyName,
            'description' => $description,
            'status' => 'DRAFT',
            'createdAt' => $now,
            'updatedAt' => $now,
        ];

        $this->database->beginTransaction();
        try {
            $this->policies->insert($record);
            $this->audit->log('AccessPolicy', $policyId, 'CREATE_ACCESS_POLICY', $actor, $correlationId, ['policyCode' => $policyCode]);
            $this->database->commit();
        } catch (\Throwable $exception) {
            $this->database->rollBack();
            throw $exception;
        }

        return $record;
    }

    public function get(string $policyId): array
    {
        $policy = $this->policies->findById($policyId);
        if ($policy === null) {
            throw new ApiException('NOT_FOUND', 'access policy not found', 404);
        }

        return $policy;
    }

    /** @return list<array<string, mixed>> */
    public function list(): array
    {
        return $this->policies->findAll();
    }

    /** @param array<string, mixed> $payload */
    public function update(string $policyId, array $payload, ActorContext $actor, string $correlationId): array
    {
        $policy = $this->get($policyId);
        if ((string) $policy['status'] !== 'DRAFT') {
            throw new ApiException('VALIDATION_ERROR', 'only draft access policies can be updated', 400);
        }

        $updates = [];
        $details = [];
        foreach (['policyName', 'description'] as $field) {
            if (array_key_exists($field, $payload)) {
                $value = trim((string) $payload[$field]);
                if ($field === 'policyName' && $value === '') {
                    throw new ApiException('VALIDATION_ERROR', 'policyName cannot be empty', 400);
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
            $this->policies->update($policyId, $updates);
            $this->audit->log('AccessPolicy', $policyId, 'UPDATE_ACCESS_POLICY', $actor, $correlationId, $details);
            $this->database->commit();
        } catch (\Throwable $exception) {
            $this->database->rollBack();
            throw $exception;
        }

        return $this->get($policyId);
    }

    public function activate(string $policyId, ActorContext $actor, string $correlationId): array
    {
        return $this->transition($policyId, 'ACTIVE', 'ACTIVATE_ACCESS_POLICY', $actor, $correlationId);
    }

    public function retire(string $policyId, ActorContext $actor, string $correlationId): array
    {
        return $this->transition($policyId, 'RETIRED', 'RETIRE_ACCESS_POLICY', $actor, $correlationId);
    }

    private function transition(string $policyId, string $targetStatus, string $auditAction, ActorContext $actor, string $correlationId): array
    {
        $policy = $this->get($policyId);
        StatusTransition::assertAccessPolicy((string) $policy['status'], $targetStatus);

        $this->database->beginTransaction();
        try {
            $this->policies->update($policyId, [
                'status' => $targetStatus,
                'updatedAt' => $this->clock->nowUtc(),
            ]);
            $this->audit->log('AccessPolicy', $policyId, $auditAction, $actor, $correlationId, [
                'fromStatus' => $policy['status'],
                'toStatus' => $targetStatus,
            ]);
            $this->database->commit();
        } catch (\Throwable $exception) {
            $this->database->rollBack();
            throw $exception;
        }

        return $this->get($policyId);
    }
}
