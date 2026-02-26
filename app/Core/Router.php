<?php

namespace App\Core;

use App\Http\Request;
use App\Http\Response;

final class Router
{
    /** @var Container */
    private $container;

    /** @var array<string, array<int, array{pattern:string, regex:string, handler:callable|array}>> */
    private $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param callable|array{0:class-string,1:string} $handler
     */
    public function get(string $pattern, $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    /**
     * @param callable|array{0:class-string,1:string} $handler
     */
    public function post(string $pattern, $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    /**
     * @param callable|array{0:class-string,1:string} $handler
     */
    private function add(string $method, string $pattern, $handler): void
    {
        $pattern = $pattern === '' ? '/' : $pattern;
        $pattern = rtrim($pattern, '/') ?: '/';

        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        $this->routes[$method][] = [
            'pattern' => $pattern,
            'regex' => $regex,
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $path = $request->path();

        $candidates = $this->routes[$method] ?? [];
        foreach ($candidates as $route) {
            if (!preg_match($route['regex'], $path, $matches)) {
                continue;
            }

            $params = [];
            foreach ($matches as $k => $v) {
                if (is_string($k)) {
                    $params[$k] = $v;
                }
            }

            return $this->invoke($route['handler'], $request, $params);
        }

        return new Response('Not found', 404, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /**
     * @param callable|array{0:class-string,1:string} $handler
     * @param array<string, string> $params
     */
    private function invoke($handler, Request $request, array $params): Response
    {
        if (is_array($handler)) {
            $class = $handler[0];
            $method = $handler[1];
            $controller = $this->container->get($class);
            return $controller->$method($request, $params);
        }

        return $handler($request, $params);
    }
}

