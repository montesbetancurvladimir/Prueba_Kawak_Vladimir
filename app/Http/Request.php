<?php

namespace App\Http;

final class Request
{
    /** @var string */
    private $method;
    /** @var string */
    private $path;
    /** @var array<string, string> */
    private $query;
    /** @var array<string, mixed> */
    private $post;

    private function __construct(string $method, string $path, array $query, array $post)
    {
        $this->method = strtoupper($method);
        $this->path = $path;
        $this->query = $query;
        $this->post = $post;
    }

    public static function fromGlobals(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        // Support both vhost to /public and /project/public/ style URLs
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $scriptDir = str_replace('\\', '/', dirname($scriptName));
        $base = rtrim($scriptDir, '/');
        if ($base !== '' && $base !== '.' && $base !== '/') {
            if (strpos($path, $base) === 0) {
                $path = substr($path, strlen($base));
                if ($path === '') {
                    $path = '/';
                }
            }
        }

        $path = rtrim($path, '/') ?: '/';

        return new self($method, $path, $_GET ?? [], $_POST ?? []);
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    /** @return array<string, string> */
    public function query(): array
    {
        return $this->query;
    }

    public function queryString(string $key, string $default = ''): string
    {
        $v = $this->query[$key] ?? $default;
        return is_string($v) ? $v : $default;
    }

    /** @return array<string, mixed> */
    public function post(): array
    {
        return $this->post;
    }

    public function postString(string $key, string $default = ''): string
    {
        $v = $this->post[$key] ?? $default;
        return is_string($v) ? $v : $default;
    }

    public function postInt(string $key, int $default = 0): int
    {
        $v = $this->post[$key] ?? null;
        if (is_int($v)) {
            return $v;
        }
        if (is_string($v) && preg_match('/^\d+$/', $v)) {
            return (int) $v;
        }
        return $default;
    }
}

