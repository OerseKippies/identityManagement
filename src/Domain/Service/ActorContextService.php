<?php

declare(strict_types=1);

namespace IdM\Domain\Service;

use IdM\Http\ApiException;
use IdM\Infrastructure\Uuid;
use IdM\Repository\AssignmentRepository;
use IdM\Repository\ServiceAccountRepository;
use IdM\Repository\UserRepository;

final class ActorContextService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly ServiceAccountRepository $serviceAccounts,
        private readonly AssignmentRepository $assignments
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function resolve(array $payload, string $correlationId): array
    {
        $credentialType = strtoupper(trim((string) ($payload['credentialType'] ?? 'USER')));
        $subjectHint = isset($payload['subjectHint']) ? trim((string) $payload['subjectHint']) : null;

        if ($credentialType === 'SERVICE_ACCOUNT') {
            return $this->resolveServiceAccount($subjectHint, $correlationId);
        }

        return $this->resolveUser($subjectHint, $correlationId);
    }

    private function resolveUser(?string $subjectHint, string $correlationId): array
    {
        if ($subjectHint === null || !Uuid::isValid($subjectHint)) {
            throw new ApiException('VALIDATION_ERROR', 'subjectHint must be a valid user UUID', 400);
        }

        $user = $this->users->findById($subjectHint);
        if ($user === null) {
            throw new ApiException('NOT_FOUND', 'user not found', 404);
        }

        $roleCodes = $this->assignments->listRoleCodesForUser($subjectHint);
        $permissionCodes = $this->assignments->listPermissionCodesForUser($subjectHint);

        return [
            'actorType' => 'USER',
            'actorId' => (string) $user['userId'],
            'displayName' => (string) $user['displayName'],
            'status' => (string) $user['status'],
            'roles' => $roleCodes,
            'permissions' => $permissionCodes,
            'tokenReferenceId' => null,
            'correlationId' => $correlationId,
        ];
    }

    private function resolveServiceAccount(?string $subjectHint, string $correlationId): array
    {
        if ($subjectHint === null || !Uuid::isValid($subjectHint)) {
            throw new ApiException('VALIDATION_ERROR', 'subjectHint must be a valid service account UUID', 400);
        }

        $account = $this->serviceAccounts->findById($subjectHint);
        if ($account === null) {
            throw new ApiException('NOT_FOUND', 'service account not found', 404);
        }

        return [
            'actorType' => 'SERVICE_ACCOUNT',
            'actorId' => (string) $account['serviceAccountId'],
            'displayName' => (string) $account['accountName'],
            'status' => (string) $account['status'],
            'roles' => [],
            'permissions' => [],
            'tokenReferenceId' => null,
            'correlationId' => $correlationId,
        ];
    }
}
