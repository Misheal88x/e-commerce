<?php

declare(strict_types=1);

namespace Core;

use App\Exceptions\NotFoundException;

class Router
{
    private array $routes          = [];
    private array $globalMiddleware = [];

    public function get(string $uri, array $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $uri, $handler, $middleware);
    }

    public function post(string $uri, array $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $uri, $handler, $middleware);
    }

    public function put(string $uri, array $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $uri, $handler, $middleware);
    }

    public function patch(string $uri, array $handler, array $middleware = []): void
    {
        $this->addRoute('PATCH', $uri, $handler, $middleware);
    }

    public function delete(string $uri, array $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $uri, $handler, $middleware);
    }

    private function addRoute(string $method, string $uri, array $handler, array $middleware): void
    {
        $this->routes[] = [
            'method'     => $method,
            'uri'        => $uri,
            'handler'    => $handler,
            'middleware' => $middleware,
            'pattern'    => $this->uriToPattern($uri),
        ];
    }

    private function uriToPattern(string $uri): string
    {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $uri);
        return '#^' . $pattern . '$#';
    }

    public function setGlobalMiddleware(array $middleware): void
    {
        $this->globalMiddleware = $middleware;
    }

    public function dispatch(Request $request): Response
    {
        // Handle preflight OPTIONS request
        if ($request->method() === 'OPTIONS') {
            return Response::empty(204)->withHeader('Allow', 'GET,POST,PUT,DELETE,OPTIONS');
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method()) {
                continue;
            }

            if (!preg_match($route['pattern'], $request->uri(), $matches)) {
                continue;
            }

            // Extract named route params
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            $request->setParams($params);

            // Build middleware stack: global + route-level
            $allMiddleware = array_merge($this->globalMiddleware, $route['middleware']);

            $pipeline = new Pipeline();

            return $pipeline->pipe($allMiddleware)->run(
                $request,
                fn(Request $req) => $this->callHandler($route['handler'], $req)
            );
        }

        throw new NotFoundException("Route [{$request->method()} {$request->uri()}] not found.");
    }

    private function callHandler(array $handler, Request $request): Response
    {
        [$controllerClass, $method] = $handler;

        $container  = Application::getInstance()->getContainer();
        $controller = $container->make($controllerClass);

        if (!method_exists($controller, $method)) {
            throw new \RuntimeException("Method [{$method}] not found on [{$controllerClass}].");
        }

        return $controller->$method($request);
    }
}
