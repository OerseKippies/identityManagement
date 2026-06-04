<?php

declare(strict_types=1);

namespace Idm\Domain;

use Idm\Repository\ServiceAccountRepository;
use Idm\Support\Clock;
use Idm\Support\Uuid;
use Idm\Support\Validator;
use RuntimeException;

final class ServiceAccountService
{
    private ServiceAccountRepository $repository;
    private AuditService $audit;

    public function __construct(ServiceAccountRepository $repository, AuditService $audit)
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
        Validator::uuid($id, 'serviceAccountId');
        return $this->repository->find($id);
    }

    public function create(array $data): array
    {
        Validator::requireFields($data, ['accountName']);
        if ($this->repository->existsBy('accountName', (string) $data['accountName'])) {
            throw new RuntimeException('Service account name already exists', 409);
        }
        $now = Clock::dbNow();
        $row = $this->repository->insert([
            'serviceAccountId' => Uuid::v4(),
            'accountName' => (string) $data['accountName'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'PENDING',
            'createdAt' => $now,
            'updatedAt' => $now,
        ]);
        $this->audit->record('ServiceAccount', $row['serviceAccountId'], 'CREATE', $row);

        return $row;
    }

    public function update(string $id, array $data): array
    {
        $this->get($id);
        $update = [];
        foreach (['description'] as $field) {
            if (array_key_exists($field, $data)) {
                $update[$field] = $data[$field];
            }
        }
        $update['updatedAt'] = Clock::dbNow();
        $row = $this->repository->update($id, $update);
        $this->audit->record('ServiceAccount', $id, 'UPDATE', $update);

        return $row;
    }

    public function transition(string $id, string $to, string $action): array
    {
        $current = $this->get($id);
        $from = $current['status'];
        $allowed = [
            'PENDING' => ['ACTIVE'],
            'ACTIVE' => ['DISABLED', 'LOCKED'],
            'LOCKED' => ['ACTIVE'],
            'DISABLED' => ['ACTIVE'],
        ];
        if (!in_array($to, $allowed[$from] ?? [], true)) {
            throw new RuntimeException("Invalid status transition {$from} -> {$to}", 409);
        }
        $row = $this->repository->update($id, ['status' => $to, 'updatedAt' => Clock::dbNow()]);
        $this->audit->record('ServiceAccount', $id, $action, ['from' => $from, 'to' => $to]);

        return $row;
    }
}
