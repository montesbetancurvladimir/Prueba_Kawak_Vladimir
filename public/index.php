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
use App\Core\Router;
use App\Http\Request;
use App\Http\Response;

session_start();

if (!function_exists('e')) {
    function e($v): string
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}

set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    $container = new Container();
    $router = new Router($container);

    $router->get('/', [App\Controllers\DocumentController::class, 'index']);

    $router->get('/login', [App\Controllers\AuthController::class, 'showLogin']);
    $router->post('/login', [App\Controllers\AuthController::class, 'login']);
    $router->post('/logout', [App\Controllers\AuthController::class, 'logout']);

    $router->get('/documents', [App\Controllers\DocumentController::class, 'index']);
    $router->get('/documents/create', [App\Controllers\DocumentController::class, 'createForm']);
    $router->post('/documents/create', [App\Controllers\DocumentController::class, 'create']);
    $router->get('/documents/{id}/edit', [App\Controllers\DocumentController::class, 'editForm']);
    $router->post('/documents/{id}/edit', [App\Controllers\DocumentController::class, 'update']);
    $router->post('/documents/{id}/delete', [App\Controllers\DocumentController::class, 'delete']);

    $request = Request::fromGlobals();
    $response = $router->dispatch($request);
} catch (Throwable $e) {
    error_log((string) $e);
    $body = 'Error interno.';
    $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
    if ($remoteAddr === '127.0.0.1' || $remoteAddr === '::1') {
        $body .= "\n\n" . $e->getMessage();
    }
    $response = new Response($body, 500, ['Content-Type' => 'text/plain; charset=UTF-8']);
}

$response->send();

