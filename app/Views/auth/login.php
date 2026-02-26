<?php
/** @var string $csrfToken */
/** @var string $error */
/** @var string $username */
?>

<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-4">
        <h2 class="h4 mb-3">Iniciar sesión</h2>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger" role="alert">
                <strong>Error:</strong> <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="/login" class="vstack gap-3">
            <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">

            <div>
                <label class="form-label">Usuario</label>
                <input class="form-control" name="username" value="<?= e($username) ?>" autocomplete="username" required>
            </div>

            <div>
                <label class="form-label">Contraseña</label>
                <input class="form-control" name="password" type="password" autocomplete="current-password" required>
            </div>

            <button class="btn btn-primary w-100" type="submit">Ingresar</button>
        </form>
    </div>
</div>

