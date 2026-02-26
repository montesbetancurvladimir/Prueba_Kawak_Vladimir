<?php

namespace App\Core;

use App\Controllers\AuthController;
use App\Controllers\DocumentController;
use App\Core\Security\Auth;
use App\Core\Security\Csrf;
use App\Database\Connection;
use App\Core\View;
use App\Repositories\DocumentRepository;
use App\Repositories\ProcessRepository;
use App\Repositories\TypeRepository;

final class Container
{
    /** @var array<string, mixed> */
    private $instances = [];

    public function get(string $id)
    {
        if (array_key_exists($id, $this->instances)) {
            return $this->instances[$id];
        }

        switch ($id) {
            case Connection::class:
                $cfg = require __DIR__ . '/../../config/database.php';
                $this->instances[$id] = new Connection($cfg['dsn'], $cfg['user'], $cfg['password']);
                return $this->instances[$id];

            case Csrf::class:
                $this->instances[$id] = new Csrf();
                return $this->instances[$id];

            case Auth::class:
                $authCfg = require __DIR__ . '/../../config/auth.php';
                $this->instances[$id] = new Auth($authCfg['username'], $authCfg['password']);
                return $this->instances[$id];

            case ProcessRepository::class:
                $this->instances[$id] = new ProcessRepository($this->get(Connection::class));
                return $this->instances[$id];

            case TypeRepository::class:
                $this->instances[$id] = new TypeRepository($this->get(Connection::class));
                return $this->instances[$id];

            case DocumentRepository::class:
                $this->instances[$id] = new DocumentRepository(
                    $this->get(Connection::class),
                    $this->get(TypeRepository::class),
                    $this->get(ProcessRepository::class)
                );
                return $this->instances[$id];

            case View::class:
                $this->instances[$id] = new View(__DIR__ . '/../Views');
                return $this->instances[$id];

            case Flash::class:
                $this->instances[$id] = new Flash();
                return $this->instances[$id];

            case AuthController::class:
                $this->instances[$id] = new AuthController(
                    $this->get(View::class),
                    $this->get(Auth::class),
                    $this->get(Csrf::class),
                    $this->get(Flash::class)
                );
                return $this->instances[$id];

            case DocumentController::class:
                $this->instances[$id] = new DocumentController(
                    $this->get(View::class),
                    $this->get(Auth::class),
                    $this->get(Csrf::class),
                    $this->get(Flash::class),
                    $this->get(DocumentRepository::class),
                    $this->get(TypeRepository::class),
                    $this->get(ProcessRepository::class)
                );
                return $this->instances[$id];

            default:
                if (class_exists($id)) {
                    $this->instances[$id] = new $id();
                    return $this->instances[$id];
                }
                throw new \RuntimeException('Service not found: ' . $id);
        }
    }
}

