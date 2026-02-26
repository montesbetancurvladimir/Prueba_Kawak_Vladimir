<?php
/** @var string $content */
/** @var string $csrfToken */
/** @var string $authUser */
/** @var array{type:string,message:string}|null $flash */

if (!function_exists('e')) {
    function e($v): string
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}

$flashType = $flash['type'] ?? '';
$flashClass = 'info';
if ($flashType === 'success') {
    $flashClass = 'success';
} elseif ($flashType === 'danger' || $flashType === 'error') {
    $flashClass = 'danger';
} elseif ($flashType === 'warning') {
    $flashClass = 'warning';
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CRUD Documentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body { background: #f8f9fa; }
        .app-shell { min-height: 100vh; }
        .card { border: 0; box-shadow: 0 0.25rem 1rem rgba(0,0,0,.05); }
        .table thead th { white-space: nowrap; }
    </style>
</head>
<body class="app-shell">
    <header class="mb-4">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="/documents">KAWAK Docs</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="nav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item"><a class="nav-link" href="/documents">Documentos</a></li>
                    </ul>
                    <div class="d-flex align-items-center gap-2">
                        <?php if ($authUser !== ''): ?>
                            <span class="navbar-text text-white-50">Usuario: <?= e($authUser) ?></span>
                            <form method="post" action="/logout" class="m-0">
                                <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                                <button class="btn btn-outline-light btn-sm" type="submit">Salir</button>
                            </form>
                        <?php else: ?>
                            <a class="btn btn-outline-light btn-sm" href="/login">Login</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main class="container pb-5">
        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flashClass) ?> d-flex align-items-center justify-content-between" role="alert">
                <div><strong><?= e($flash['type']) ?>:</strong> <?= e($flash['message']) ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <?= $content ?>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>

