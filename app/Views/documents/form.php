<?php
/** @var string $csrfToken */
/** @var string $mode */
/** @var array<string,mixed> $doc */
/** @var array<int,array<string,mixed>> $types */
/** @var array<int,array<string,mixed>> $processes */
/** @var string $error */

$isEdit = $mode === 'edit';
$action = $isEdit ? ('/documents/' . (int)($doc['DOC_ID'] ?? 0) . '/edit') : '/documents/create';
?>

<h2><?= $isEdit ? 'Editar documento' : 'Crear documento' ?></h2>

<?php if ($error !== ''): ?>
    <div class="alert alert-danger" role="alert">
        <strong>Error:</strong> <?= e($error) ?>
    </div>
<?php endif; ?>

<?php if ($isEdit): ?>
    <div class="alert alert-light border d-flex flex-wrap gap-3 align-items-center" role="alert">
        <div><strong>Código actual:</strong> <span class="font-monospace"><?= e($doc['DOC_CODIGO'] ?? '') ?></span></div>
        <div><strong>Consecutivo:</strong> <span class="font-monospace"><?= e($doc['DOC_CONSECUTIVO'] ?? '') ?></span></div>
    </div>
<?php endif; ?>

<form method="post" action="<?= e($action) ?>" class="vstack gap-3">
    <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">

    <div>
        <label class="form-label">Nombre</label>
        <input class="form-control" name="DOC_NOMBRE" value="<?= e($doc['DOC_NOMBRE'] ?? '') ?>" required>
    </div>

    <div>
        <label class="form-label">Tipo de documento</label>
        <select class="form-select" name="DOC_ID_TIPO" required>
            <option value="">Seleccione...</option>
            <?php foreach ($types as $t): ?>
                <?php $sel = ((int)($doc['DOC_ID_TIPO'] ?? 0) === (int)$t['TIP_ID']) ? 'selected' : ''; ?>
                <option value="<?= e($t['TIP_ID']) ?>" <?= $sel ?>>
                    <?= e($t['TIP_PREFIJO']) ?> - <?= e($t['TIP_NOMBRE']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label class="form-label">Proceso</label>
        <select class="form-select" name="DOC_ID_PROCESO" required>
            <option value="">Seleccione...</option>
            <?php foreach ($processes as $p): ?>
                <?php $sel = ((int)($doc['DOC_ID_PROCESO'] ?? 0) === (int)$p['PRO_ID']) ? 'selected' : ''; ?>
                <option value="<?= e($p['PRO_ID']) ?>" <?= $sel ?>>
                    <?= e($p['PRO_PREFIJO']) ?> - <?= e($p['PRO_NOMBRE']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label class="form-label">Contenido</label>
        <textarea class="form-control" name="DOC_CONTENIDO" rows="10"><?= e($doc['DOC_CONTENIDO'] ?? '') ?></textarea>
    </div>

    <div class="d-flex flex-wrap gap-2">
        <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Guardar cambios' : 'Crear' ?></button>
        <a class="btn btn-outline-secondary" href="/documents">Volver</a>
    </div>
</form>

