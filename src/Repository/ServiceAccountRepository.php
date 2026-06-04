<?php

declare(strict_types=1);

namespace Idm\Repository;

use PDO;

final class ServiceAccountRepository extends BaseRepository
{
    protected string $table = 'idm_service_accounts';
    protected string $idColumn = 'serviceAccountId';

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }
}
