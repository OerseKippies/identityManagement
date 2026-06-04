<?php

declare(strict_types=1);

namespace Idm\Repository;

use PDO;

final class UserRepository extends BaseRepository
{
    protected string $table = 'idm_users';
    protected string $idColumn = 'userId';

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }
}
