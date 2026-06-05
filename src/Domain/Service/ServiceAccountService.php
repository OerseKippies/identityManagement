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
use IdM\Repository\ServiceAccountRepository;

final class ServiceAccountService
{
    public function __construct(
        private readonly Database $database,
        private readonly ServiceAccountRepository $serviceAccounts,
        private readonly AuditLogger $audit,
        private readonly Clock $clock
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function create(array $payload, ActorContext $actor, string $correlationId): array
    {
        $accountName = trim((string) ($payload['accountName'] ?? ''));
        $description = isset($payload['description']) ? trim((string) $payload['description']) : null;

        if ($accountName === '') {
            throw new ApiException('VALIDATION_ERROR', 'accountName is required', 400);
        }
        if ($this->serviceAccounts->existsByName($accountName)) {
            throw new ApiException('CONFLICT', 'accountName already exists', 409);
        }

        $serviceAccountId = Uuid::v4();
        $now = $this->clock->nowUtc();
        $record = [
            'serviceAccountId' => $serviceAccountId,
            'accountName' => $accountName,
            'description' => $description,
            'status' => 'PENDING',
            'createdAt' => $now,
            'updatedAt' => $now,
        ];

        $this->database->beginTransaction();
        try {
            $this->serviceAccounts->insert($record);
            $this->audit->log('ServiceAccount', $serviceAccountId, 'CREATE_SERVICE_ACCOUNT', $actor, $correlationId, ['accountName' => $accountName]);
            $this->database->commit();
        } catch (\Throwable $exception) {
            $this->database->rollBack();
            throw $exception;
        }

        return $record;
    }

    public function get(string $serviceAccountId): array
    {
        $account = $this->serviceAccounts->findById($serviceAccountId);
        if ($account === null) {
            throw new ApiException('NOT_FOUND', 'service account not found', 404);
        }

        return $account;
    }

    /** @return list<array<string, mixed>> */
    public function list(): array
    {
        return $this->serviceAccounts->findAll();
    }

    /** @param array<string, mixed> $payload */
    public function update(string $serviceAccountId, array $payload, ActorContext $actor, string $correlationId): array
    {
        $account = $this->get($serviceAccountId);
        $updates = [];
        $details = [];

        if (array_key_exists('description', $payload)) {
            $description = trim((string) $payload['description']);
            $updates['description'] = $description === '' ? null : $description;
            $details['description'] = $updates['description'];
        }

        if ($updates === []) {
            throw new ApiException('VALIDATION_ERROR', 'no updatable fields provided', 400);
        }

        $updates['updatedAt'] = $this->clock->nowUtc();

        $this->database->beginTransaction();
        try {
            $this->serviceAccounts->update($serviceAccountId, $updates);
            $this->audit->log('ServiceAccount', $serviceAccountId, 'UPDATE_SERVICE_ACCOUNT', $actor, $correlationId, $details);
            $this->database->commit();
        } catch (\Throwable $exception) {
            $this->database->rollBack();
            throw $exception;
        }

        return $this->get($serviceAccountId);
    }

    public function transition(string $serviceAccountId, string $targetStatus, string $auditAction, ActorContext $actor, string $correlationId): array
    {
        $account = $this->get($serviceAccountId);
        StatusTransition::assertUserOrServiceAccount((string) $account['status'], $targetStatus);

        $this->database->beginTransaction();
        try {
            $this->serviceAccounts->update($serviceAccountId, [
                'status' => $targetStatus,
                'updatedAt' => $this->clock->nowUtc(),
            ]);
            $this->audit->log('ServiceAccount', $serviceAccountId, $auditAction, $actor, $correlationId, [
                'fromStatus' => $account['status'],
                'toStatus' => $targetStatus,
            ]);
            $this->database->commit();
        } catch (\Throwable $exception) {
            $this->database->rollBack();
            throw $exception;
        }

        return $this->get($serviceAccountId);
    }
}
