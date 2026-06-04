<?php

declare(strict_types=1);

namespace Idm\Domain;

use Idm\Repository\TokenReferenceRepository;
use Idm\Support\Clock;
use Idm\Support\Uuid;
use Idm\Support\Validator;
use RuntimeException;

final class TokenReferenceService
{
    private TokenReferenceRepository $repository;
    private AuditService $audit;

    public function __construct(TokenReferenceRepository $repository, AuditService $audit)
    {
        $this->repository = $repository;
        $this->audit = $audit;
    }

    public function list(): array
    {
        return $this->repository->list();
    }

    public function get(string $id): array
    {
        Validator::uuid($id, 'tokenReferenceId');
        return $this->repository->find($id);
    }

    public function create(array $data): array
    {
        Validator::requireFields($data, ['subjectType', 'subjectId', 'expiresAt']);
        Validator::oneOf((string) $data['subjectType'], ['USER', 'SERVICE_ACCOUNT'], 'subjectType');
        Validator::uuid((string) $data['subjectId'], 'subjectId');
        $now = Clock::dbNow();
        $row = $this->repository->insert([
            'tokenReferenceId' => Uuid::v4(),
            'subjectType' => (string) $data['subjectType'],
            'subjectId' => (string) $data['subjectId'],
            'issuedAt' => $now,
            'expiresAt' => (string) $data['expiresAt'],
            'revokedAt' => null,
            'status' => 'ACTIVE',
        ]);
        $this->audit->record('TokenReference', $row['tokenReferenceId'], 'CREATE_TOKEN_REFERENCE', $row);

        return $row;
    }

    public function revoke(string $id): array
    {
        $current = $this->get($id);
        if ($current['status'] !== 'ACTIVE') {
            throw new RuntimeException('Only active token references can be revoked', 409);
        }
        $row = $this->repository->update($id, [
            'status' => 'REVOKED',
            'revokedAt' => Clock::dbNow(),
        ]);
        $this->audit->record('TokenReference', $id, 'REVOKE_TOKEN_REFERENCE');

        return $row;
    }
}
