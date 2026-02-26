<?php

namespace App\Repositories;

use App\Database\Connection;
use PDO;
use PDOException;

final class DocumentRepository
{
    /** @var PDO */
    private $pdo;
    /** @var TypeRepository */
    private $types;
    /** @var ProcessRepository */
    private $processes;

    public function __construct(Connection $connection, TypeRepository $types, ProcessRepository $processes)
    {
        $this->pdo = $connection->pdo();
        $this->types = $types;
        $this->processes = $processes;
    }

    /** @return array<int, array<string, mixed>> */
    public function list(string $q = ''): array
    {
        $q = trim($q);

        $sql = 'SELECT d.DOC_ID, d.DOC_NOMBRE, d.DOC_CODIGO, d.DOC_CONTENIDO, d.DOC_ID_TIPO, d.DOC_ID_PROCESO,
                       t.TIP_NOMBRE, t.TIP_PREFIJO,
                       p.PRO_NOMBRE, p.PRO_PREFIJO
                FROM DOC_DOCUMENTO d
                INNER JOIN TIP_TIPO_DOC t ON t.TIP_ID = d.DOC_ID_TIPO
                INNER JOIN PRO_PROCESO p ON p.PRO_ID = d.DOC_ID_PROCESO';

        $params = [];
        if ($q !== '') {
            // When PDO::ATTR_EMULATE_PREPARES is false (native prepares),
            // some drivers do not allow reusing the same named placeholder twice.
            $sql .= ' WHERE d.DOC_NOMBRE LIKE :q_nombre OR d.DOC_CODIGO LIKE :q_codigo';
            $like = '%' . $q . '%';
            $params['q_nombre'] = $like;
            $params['q_codigo'] = $like;
        }

        $sql .= ' ORDER BY d.DOC_ID DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT DOC_ID, DOC_NOMBRE, DOC_CODIGO, DOC_CONTENIDO, DOC_ID_TIPO, DOC_ID_PROCESO, DOC_CONSECUTIVO
             FROM DOC_DOCUMENTO
             WHERE DOC_ID = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function create(string $nombre, string $contenido, int $tipoId, int $procesoId): int
    {
        $nombre = trim($nombre);
        if ($nombre === '') {
            throw new \InvalidArgumentException('Nombre requerido');
        }

        $tipo = $this->types->find($tipoId);
        $proceso = $this->processes->find($procesoId);
        if ($tipo === null || $proceso === null) {
            throw new \InvalidArgumentException('Tipo o proceso inválido');
        }
        $tipPrefijo = (string) $tipo['TIP_PREFIJO'];
        $proPrefijo = (string) $proceso['PRO_PREFIJO'];

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO DOC_DOCUMENTO (DOC_NOMBRE, DOC_CODIGO, DOC_CONSECUTIVO, DOC_CONTENIDO, DOC_ID_TIPO, DOC_ID_PROCESO)
                 VALUES (:nombre, :codigo, :consecutivo, :contenido, :tipo, :proceso)'
            );

            for ($attempt = 0; $attempt < 10; $attempt++) {
                $next = $this->nextConsecutive($tipoId, $procesoId);
                $codigo = $tipPrefijo . '-' . $proPrefijo . '-' . $next;

                try {
                    $stmt->execute([
                        'nombre' => $nombre,
                        'codigo' => $codigo,
                        'consecutivo' => $next,
                        'contenido' => $contenido,
                        'tipo' => $tipoId,
                        'proceso' => $procesoId,
                    ]);

                    $id = (int) $this->pdo->lastInsertId();
                    $this->pdo->commit();
                    return $id;
                } catch (PDOException $e) {
                    if (!$this->isDuplicateKey($e)) {
                        throw $e;
                    }
                    // retry with a new consecutivo
                }
            }

            throw new \RuntimeException('No se pudo asignar consecutivo');
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function update(int $id, string $nombre, string $contenido, int $tipoId, int $procesoId): void
    {
        $doc = $this->find($id);
        if ($doc === null) {
            throw new \RuntimeException('Documento no encontrado');
        }

        $nombre = trim($nombre);
        if ($nombre === '') {
            throw new \InvalidArgumentException('Nombre requerido');
        }

        $currentTipo = (int) $doc['DOC_ID_TIPO'];
        $currentProceso = (int) $doc['DOC_ID_PROCESO'];

        $this->pdo->beginTransaction();
        try {
            $codigo = (string) $doc['DOC_CODIGO'];
            $consecutivo = (int) $doc['DOC_CONSECUTIVO'];

            $stmt = $this->pdo->prepare(
                'UPDATE DOC_DOCUMENTO
                 SET DOC_NOMBRE = :nombre,
                     DOC_CODIGO = :codigo,
                     DOC_CONSECUTIVO = :consecutivo,
                     DOC_CONTENIDO = :contenido,
                     DOC_ID_TIPO = :tipo,
                     DOC_ID_PROCESO = :proceso
                 WHERE DOC_ID = :id'
            );

            if ($tipoId !== $currentTipo || $procesoId !== $currentProceso) {
                $tipo = $this->types->find($tipoId);
                $proceso = $this->processes->find($procesoId);
                if ($tipo === null || $proceso === null) {
                    throw new \InvalidArgumentException('Tipo o proceso inválido');
                }
                $tipPrefijo = (string) $tipo['TIP_PREFIJO'];
                $proPrefijo = (string) $proceso['PRO_PREFIJO'];

                for ($attempt = 0; $attempt < 10; $attempt++) {
                    $next = $this->nextConsecutive($tipoId, $procesoId);
                    $codigo = $tipPrefijo . '-' . $proPrefijo . '-' . $next;
                    $consecutivo = $next;

                    try {
                        $stmt->execute([
                            'nombre' => $nombre,
                            'codigo' => $codigo,
                            'consecutivo' => $consecutivo,
                            'contenido' => $contenido,
                            'tipo' => $tipoId,
                            'proceso' => $procesoId,
                            'id' => $id,
                        ]);
                        $this->pdo->commit();
                        return;
                    } catch (PDOException $e) {
                        if (!$this->isDuplicateKey($e)) {
                            throw $e;
                        }
                    }
                }

                throw new \RuntimeException('No se pudo asignar consecutivo');
            }

            $stmt->execute([
                'nombre' => $nombre,
                'codigo' => $codigo,
                'consecutivo' => $consecutivo,
                'contenido' => $contenido,
                'tipo' => $tipoId,
                'proceso' => $procesoId,
                'id' => $id,
            ]);

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM DOC_DOCUMENTO WHERE DOC_ID = :id');
        $stmt->execute(['id' => $id]);
    }

    private function nextConsecutive(int $tipoId, int $procesoId): int
    {
        $stmtMax = $this->pdo->prepare(
            'SELECT COALESCE(MAX(DOC_CONSECUTIVO), 0) AS max_consec
             FROM DOC_DOCUMENTO
             WHERE DOC_ID_TIPO = :tipo AND DOC_ID_PROCESO = :proceso'
        );
        $stmtMax->execute(['tipo' => $tipoId, 'proceso' => $procesoId]);
        $max = (int) (($stmtMax->fetch()['max_consec'] ?? 0));
        return $max + 1;
    }

    private function isDuplicateKey(PDOException $e): bool
    {
        return $e->getCode() === '23000';
    }
}

