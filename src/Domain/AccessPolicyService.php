<?php

declare(strict_types=1);

namespace Idm\Domain;

use Idm\Repository\AccessPolicyRepository;
use Idm\Support\Clock;
use Idm\Support\Uuid;
use Idm\Support\Validator;
use RuntimeException;

final class AccessPolicyService
{
    private AccessPolicyRepository $repository;
    private AuditService $audit;

    public function __construct(AccessPolicyRepository $repository, AuditService $audit)
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
        Validator::uuid($id, 'policyId');
        return $this->repository->find($id);
    }

    public function create(array $data): array
    {
        Validator::requireFields($data, ['policyCode', 'policyName']);
        if ($this->repository->existsBy('policyCode', (string) $data['policyCode'])) {
            throw new RuntimeException('Policy code already exists', 409);
        }
        $now = Clock::dbNow();
        $row = $this->repository->insert([
            'policyId' => Uuid::v4(),
            'policyCode' => (string) $data['policyCode'],
            'policyName' => (string) $data['policyName'],
            'description' => $data['description'] ?? null,
            'status' => 'DRAFT',
            'createdAt' => $now,
            'updatedAt' => $now,
        ]);
        $this->audit->record('AccessPolicy', $row['policyId'], 'CREATE', $row);

        return $row;
    }

    public function update(string $id, array $data): array
    {
        $this->get($id);
        $update = [];
        foreach (['policyName', 'description'] as $field) {
            if (array_key_exists($field, $data)) {
                $update[$field] = $data[$field];
            }
        }
        $update['updatedAt'] = Clock::dbNow();
        $row = $this->repository->update($id, $update);
        $this->audit->record('AccessPolicy', $id, 'UPDATE', $update);

        return $row;
    }

    public function transition(string $id, string $to, string $action): array
    {
        $current = $this->get($id);
        $from = $current['status'];
        $allowed = [
            'DRAFT' => ['ACTIVE'],
            'ACTIVE' => ['RETIRED'],
        ];
        if (!in_array($to, $allowed[$from] ?? [], true)) {
            throw new RuntimeException("Invalid status transition {$from} -> {$to}", 409);
        }
        $row = $this->repository->update($id, ['status' => $to, 'updatedAt' => Clock::dbNow()]);
        $this->audit->record('AccessPolicy', $id, $action, ['from' => $from, 'to' => $to]);

        return $row;
    }
}
