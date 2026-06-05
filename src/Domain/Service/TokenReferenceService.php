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
use IdM\Repository\TokenReferenceRepository;
use IdM\Repository\UserRepository;

final class TokenReferenceService
{
    public function __construct(
        private readonly Database $database,
        private readonly TokenReferenceRepository $tokenReferences,
        private readonly UserRepository $users,
        private readonly ServiceAccountRepository $serviceAccounts,
        private readonly AuditLogger $audit,
        private readonly Clock $clock
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function create(array $payload, ActorContext $actor, string $correlationId): array
    {
        $subjectType = strtoupper(trim((string) ($payload['subjectType'] ?? '')));
        $subjectId = trim((string) ($payload['subjectId'] ?? ''));
        $expiresAt = trim((string) ($payload['expiresAt'] ?? ''));

        if (!in_array($subjectType, ['USER', 'SERVICE_ACCOUNT'], true)) {
            throw new ApiException('VALIDATION_ERROR', 'subjectType must be USER or SERVICE_ACCOUNT', 400);
        }
        if (!Uuid::isValid($subjectId)) {
            throw new ApiException('VALIDATION_ERROR', 'subjectId must be a valid UUID', 400);
        }
        if ($expiresAt === '') {
            throw new ApiException('VALIDATION_ERROR', 'expiresAt is required', 400);
        }

        $this->assertSubjectExists($subjectType, $subjectId);

        $tokenReferenceId = Uuid::v4();
        $issuedAt = $this->clock->nowUtc();
        $record = [
            'tokenReferenceId' => $tokenReferenceId,
            'subjectType' => $subjectType,
            'subjectId' => strtolower($subjectId),
            'issuedAt' => $issuedAt,
            'expiresAt' => $expiresAt,
            'revokedAt' => null,
            'status' => 'ACTIVE',
        ];

        $this->database->beginTransaction();
        try {
            $this->tokenReferences->insert($record);
            $this->audit->log('TokenReference', $tokenReferenceId, 'CREATE_TOKEN_REFERENCE', $actor, $correlationId, [
                'subjectType' => $subjectType,
                'subjectId' => $subjectId,
            ]);
            $this->database->commit();
        } catch (\Throwable $exception) {
            $this->database->rollBack();
            throw $exception;
        }

        return $record;
    }

    public function get(string $tokenReferenceId): array
    {
        $token = $this->tokenReferences->findById($tokenReferenceId);
        if ($token === null) {
            throw new ApiException('NOT_FOUND', 'token reference not found', 404);
        }

        return $token;
    }

    /** @return list<array<string, mixed>> */
    public function list(): array
    {
        return $this->tokenReferences->findAll();
    }

    public function revoke(string $tokenReferenceId, ActorContext $actor, string $correlationId): array
    {
        $token = $this->get($tokenReferenceId);
        if ((string) $token['status'] === 'REVOKED') {
            return $token;
        }

        StatusTransition::assertTokenReference((string) $token['status'], 'REVOKED');

        $this->database->beginTransaction();
        try {
            $this->tokenReferences->update($tokenReferenceId, [
                'status' => 'REVOKED',
                'revokedAt' => $this->clock->nowUtc(),
            ]);
            $this->audit->log('TokenReference', $tokenReferenceId, 'REVOKE_TOKEN_REFERENCE', $actor, $correlationId);
            $this->database->commit();
        } catch (\Throwable $exception) {
            $this->database->rollBack();
            throw $exception;
        }

        return $this->get($tokenReferenceId);
    }

    private function assertSubjectExists(string $subjectType, string $subjectId): void
    {
        if ($subjectType === 'USER' && $this->users->findById($subjectId) === null) {
            throw new ApiException('NOT_FOUND', 'user subject not found', 404);
        }

        if ($subjectType === 'SERVICE_ACCOUNT' && $this->serviceAccounts->findById($subjectId) === null) {
            throw new ApiException('NOT_FOUND', 'service account subject not found', 404);
        }
    }
}
