<?php

namespace App\Repositories;

use App\Database\Connection;
use PDO;

final class ProcessRepository
{
    /** @var PDO */
    private $pdo;

    public function __construct(Connection $connection)
    {
        $this->pdo = $connection->pdo();
    }

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT PRO_ID, PRO_NOMBRE, PRO_PREFIJO FROM PRO_PROCESO ORDER BY PRO_NOMBRE ASC');
        return $stmt->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT PRO_ID, PRO_NOMBRE, PRO_PREFIJO FROM PRO_PROCESO WHERE PRO_ID = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }
}

