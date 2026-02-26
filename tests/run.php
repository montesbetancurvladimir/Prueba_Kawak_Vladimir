<?php

declare(strict_types=1);

$autoload = __DIR__ . '/../vendor/autoload.php';
if (is_file($autoload)) {
    require $autoload;
} else {
    spl_autoload_register(function (string $class): void {
        if (strpos($class, 'App\\') !== 0) {
            return;
        }
        $relative = substr($class, 4);
        $path = __DIR__ . '/../app/' . str_replace('\\', '/', $relative) . '.php';
        if (is_file($path)) {
            require $path;
        }
    });
}

use App\Core\Container;
use App\Database\Connection;
use App\Repositories\DocumentRepository;
use App\Repositories\ProcessRepository;
use App\Repositories\TypeRepository;

function assertTrue($cond, string $msg): void
{
    if (!$cond) {
        fwrite(STDERR, "FAIL: " . $msg . PHP_EOL);
        exit(1);
    }
}

$c = new Container();
/** @var Connection $conn */
$conn = $c->get(Connection::class);
$pdo = $conn->pdo();

/** @var TypeRepository $types */
$types = $c->get(TypeRepository::class);
/** @var ProcessRepository $processes */
$processes = $c->get(ProcessRepository::class);
/** @var DocumentRepository $docs */
$docs = $c->get(DocumentRepository::class);

$allTypes = $types->all();
$allProcesses = $processes->all();
assertTrue(count($allTypes) >= 1, 'Debe existir al menos un tipo (ejecuta database/dml.sql)');
assertTrue(count($allProcesses) >= 1, 'Debe existir al menos un proceso (ejecuta database/dml.sql)');

$t1 = (int)$allTypes[0]['TIP_ID'];
$p1 = (int)$allProcesses[0]['PRO_ID'];

$t2 = (int)$allTypes[count($allTypes) > 1 ? 1 : 0]['TIP_ID'];
$p2 = (int)$allProcesses[count($allProcesses) > 1 ? 1 : 0]['PRO_ID'];

// Reset documents
$pdo->exec('DELETE FROM DOC_DOCUMENTO');

$id1 = $docs->create('DOC A', 'contenido', $t1, $p1);
$d1 = $docs->find($id1);
assertTrue($d1 !== null, 'Documento 1 debe existir');
assertTrue(preg_match('/-\d+$/', (string)$d1['DOC_CODIGO']) === 1, 'Código debe terminar en -N');
assertTrue((int)$d1['DOC_CONSECUTIVO'] === 1, 'Consecutivo inicial debe ser 1');

$id2 = $docs->create('DOC B', 'contenido', $t1, $p1);
$d2 = $docs->find($id2);
assertTrue($d2 !== null, 'Documento 2 debe existir');
assertTrue((int)$d2['DOC_CONSECUTIVO'] === 2, 'Consecutivo debe incrementar');

$docs->delete($id1);
$id3 = $docs->create('DOC C', 'contenido', $t1, $p1);
$d3 = $docs->find($id3);
assertTrue($d3 !== null, 'Documento 3 debe existir');
assertTrue((int)$d3['DOC_CONSECUTIVO'] === 3, 'No debe reutilizar consecutivos después de borrar');

// Update with change of tipo/proceso should recalc code and consecutivo
$docs->update($id2, 'DOC B edit', 'nuevo', $t2, $p2);
$d2b = $docs->find($id2);
assertTrue($d2b !== null, 'Documento 2 actualizado debe existir');
assertTrue((int)$d2b['DOC_ID_TIPO'] === $t2 && (int)$d2b['DOC_ID_PROCESO'] === $p2, 'Debe cambiar tipo/proceso');
assertTrue((int)$d2b['DOC_CONSECUTIVO'] === 1, 'Para nueva combinación vacía, consecutivo debe iniciar en 1');

echo "OK" . PHP_EOL;

