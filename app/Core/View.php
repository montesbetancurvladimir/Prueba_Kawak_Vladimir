<?php

namespace App\Core;

final class View
{
    /** @var string */
    private $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/\\');
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data = []): string
    {
        $path = $this->basePath . '/' . ltrim($template, '/');
        if (!is_file($path)) {
            throw new \RuntimeException('View not found: ' . $path);
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $path;
        return (string) ob_get_clean();
    }
}

