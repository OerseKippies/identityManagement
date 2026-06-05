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
use IdM\Repository\UserRepository;

final class UserService
{
    public function __construct(
        private readonly Database $database,
        private readonly UserRepository $users,
        private readonly AuditLogger $audit,
        private readonly Clock $clock
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function create(array $payload, ActorContext $actor, string $correlationId): array
    {
        $username = trim((string) ($payload['username'] ?? ''));
        $displayName = trim((string) ($payload['displayName'] ?? ''));
        $email = trim((string) ($payload['email'] ?? ''));

        if ($username === '' || $displayName === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ApiException('VALIDATION_ERROR', 'username, displayName and valid email are required', 400);
        }

        if ($this->users->existsByUsername($username)) {
            throw new ApiException('CONFLICT', 'username already exists', 409);
        }
        if ($this->users->existsByEmail($email)) {
            throw new ApiException('CONFLICT', 'email already exists', 409);
        }

        $userId = Uuid::v4();
        $now = $this->clock->nowUtc();
        $record = [
            'userId' => $userId,
            'username' => $username,
            'displayName' => $displayName,
            'email' => $email,
            'status' => 'PENDING',
            'createdAt' => $now,
            'updatedAt' => $now,
        ];

        $this->database->beginTransaction();
        try {
            $this->users->insert($record);
            $this->audit->log('User', $userId, 'CREATE_USER', $actor, $correlationId, ['username' => $username]);
            $this->database->commit();
        } catch (\Throwable $exception) {
            $this->database->rollBack();
            throw $exception;
        }

        return $record;
    }

    public function get(string $userId): array
    {
        $user = $this->users->findById($userId);
        if ($user === null) {
            throw new ApiException('NOT_FOUND', 'user not found', 404);
        }

        return $user;
    }

    /** @return list<array<string, mixed>> */
    public function list(): array
    {
        return $this->users->findAll();
    }

    /** @param array<string, mixed> $payload */
    public function update(string $userId, array $payload, ActorContext $actor, string $correlationId): array
    {
        $user = $this->get($userId);
        $updates = [];
        $details = [];

        if (array_key_exists('displayName', $payload)) {
            $displayName = trim((string) $payload['displayName']);
            if ($displayName === '') {
                throw new ApiException('VALIDATION_ERROR', 'displayName cannot be empty', 400);
            }
            $updates['displayName'] = $displayName;
            $details['displayName'] = $displayName;
        }

        if (array_key_exists('email', $payload)) {
            $email = trim((string) $payload['email']);
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new ApiException('VALIDATION_ERROR', 'valid email is required', 400);
            }
            if ($this->users->existsByEmail($email) && $email !== $user['email']) {
                throw new ApiException('CONFLICT', 'email already exists', 409);
            }
            $updates['email'] = $email;
            $details['email'] = $email;
        }

        if ($updates === []) {
            throw new ApiException('VALIDATION_ERROR', 'no updatable fields provided', 400);
        }

        $updates['updatedAt'] = $this->clock->nowUtc();

        $this->database->beginTransaction();
        try {
            $this->users->update($userId, $updates);
            $this->audit->log('User', $userId, 'UPDATE_USER', $actor, $correlationId, $details);
            $this->database->commit();
        } catch (\Throwable $exception) {
            $this->database->rollBack();
            throw $exception;
        }

        return $this->get($userId);
    }

    public function transition(string $userId, string $targetStatus, string $auditAction, ActorContext $actor, string $correlationId): array
    {
        $user = $this->get($userId);
        StatusTransition::assertUserOrServiceAccount((string) $user['status'], $targetStatus);

        $this->database->beginTransaction();
        try {
            $this->users->update($userId, [
                'status' => $targetStatus,
                'updatedAt' => $this->clock->nowUtc(),
            ]);
            $this->audit->log('User', $userId, $auditAction, $actor, $correlationId, [
                'fromStatus' => $user['status'],
                'toStatus' => $targetStatus,
            ]);
            $this->database->commit();
        } catch (\Throwable $exception) {
            $this->database->rollBack();
            throw $exception;
        }

        return $this->get($userId);
    }
}
