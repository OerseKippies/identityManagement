<?php

declare(strict_types=1);

namespace Idm\Repository;

use PDO;

final class TokenReferenceRepository extends BaseRepository
{
    protected string $table = 'idm_token_references';
    protected string $idColumn = 'tokenReferenceId';

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }
}
