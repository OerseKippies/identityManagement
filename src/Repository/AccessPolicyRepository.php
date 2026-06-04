<?php

declare(strict_types=1);

namespace Idm\Repository;

use PDO;

final class AccessPolicyRepository extends BaseRepository
{
    protected string $table = 'idm_access_policies';
    protected string $idColumn = 'policyId';

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }
}
