<?php

namespace App\Repositories;

use App\Database\Connection;
use PDO;

final class TypeRepository
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
        $stmt = $this->pdo->query('SELECT TIP_ID, TIP_NOMBRE, TIP_PREFIJO FROM TIP_TIPO_DOC ORDER BY TIP_NOMBRE ASC');
        return $stmt->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT TIP_ID, TIP_NOMBRE, TIP_PREFIJO FROM TIP_TIPO_DOC WHERE TIP_ID = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }
}

