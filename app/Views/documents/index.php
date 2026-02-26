<?php
/** @var string $csrfToken */
/** @var string $q */
/** @var array<int, array<string,mixed>> $rows */
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <h2 class="h4 m-0">Documentos</h2>
    <a class="btn btn-primary btn-sm" href="/documents/create">Crear documento</a>
</div>

<form method="get" action="/documents" class="row g-2 align-items-end mb-3">
    <div class="col-12 col-md-6">
        <label class="form-label">Búsqueda</label>
        <input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Nombre o código">
    </div>
    <div class="col-12 col-md-auto">
        <button class="btn btn-outline-secondary" type="submit">Buscar</button>
        <?php if (trim($q) !== ''): ?>
            <a class="btn btn-link" href="/documents">Limpiar</a>
        <?php endif; ?>
    </div>
</form>

<div class="table-responsive">
<table class="table table-striped table-hover align-middle mb-0">
    <thead>
        <tr>
            <th>ID</th>
            <th>Código</th>
            <th>Nombre</th>
            <th>Tipo</th>
            <th>Proceso</th>
            <th class="text-end">Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php if (count($rows) === 0): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">Sin resultados</td></tr>
    <?php else: ?>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= e($r['DOC_ID']) ?></td>
                <td><span class="font-monospace"><?= e($r['DOC_CODIGO']) ?></span></td>
                <td><?= e($r['DOC_NOMBRE']) ?></td>
                <td><?= e($r['TIP_NOMBRE']) ?></td>
                <td><?= e($r['PRO_NOMBRE']) ?></td>
                <td class="text-end">
                    <a class="btn btn-outline-primary btn-sm" href="/documents/<?= e($r['DOC_ID']) ?>/edit">Editar</a>

                    <form method="post" action="/documents/<?= e($r['DOC_ID']) ?>/delete" class="d-inline" onsubmit="return confirm('¿Eliminar documento?');">
                        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                        <button class="btn btn-outline-danger btn-sm" type="submit">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
</div>

