<?php

namespace App;

use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\GuestMiddleware;
use App\Http\Middleware\CsrfMiddleware;
use App\Http\Request;
use App\Http\Response;
use Illuminate\Container\Container;

class Router
{
    private array $routes = [];

    private array $middlewareAliases = [
        'auth' => AuthMiddleware::class,
        'guest' => GuestMiddleware::class,
        'csrf' => CsrfMiddleware::class,
    ];

    public function __construct(private Container $container){}

    public function aliasMiddleware(string $name, string $middlewareClass): void
    {
        $this->middlewareAliases[$name] = $middlewareClass;
    }

    public function get(string $path, array $handler): RouteDefinition
    {
        return $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array $handler): RouteDefinition
    {
        return $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, array $handler): RouteDefinition
    {
        return $this->addRoute('PUT', $path, $handler);
    }

    public function patch(string $path, array $handler): RouteDefinition
    {
        return $this->addRoute('PATCH', $path, $handler);
    }

    public function delete(string $path, array $handler): RouteDefinition
    {
        return $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute(string $method, string $path, array $handler): RouteDefinition
    {
        $route = new RouteDefinition($method, $path, $handler);

        $this->routes[$method][] = $route;

        return $route;
    }

    public function dispatch(Request $request): void
    {
        $path = $request->path();
        $method = strtoupper($request->method);

        $match = $this->matchRoute($method, $path);

        if ($match) {
            [$route, $params] = $match;

            $request->setRouteParams($params);

            $this->runMiddlewarePipeline($request, $route);

            return;
        }

        if ($this->pathExistsForAnotherMethod($path)) {
            Response::json([
                'error' => 'Метод не разрешен',
            ], 405);

            return;
        }

        Response::json([
            'error' => 'Такой страницы нет',
        ], 404);
    }

    private function runMiddlewarePipeline(Request $request, RouteDefinition $route): void
    {
        $controllerAction = function (Request $request) use ($route) {
            [$controllerClass, $method] = $route->handler;

            $controller = $this->container->get($controllerClass);

            $controller->$method($request);
        };

        $pipeline = array_reduce(
            array_reverse($route->middleware),
            function ($next, $middlewareAlias) {
                return function (Request $request) use ($next, $middlewareAlias) {
                    $middlewareClass = $this->middlewareAliases[$middlewareAlias] ?? null;

                    if (!$middlewareClass) {
                        Response::json([
                            'error' => "Middleware не найден",
                        ], 500);
                        return;
                    }

                    $middleware = $this->container->get($middlewareClass);

                    $middleware->handle($request, $next);
                };
            },
            $controllerAction
        );

        $pipeline($request);
    }

    private function matchRoute(string $method, string $path): ?array
    {
        foreach ($this->routes[$method] ?? [] as $route) {
            $params = $this->matchPath($route->path, $path);

            if ($params !== null) {
                return [$route, $params];
            }
        }

        return null;
    }

    private function pathExistsForAnotherMethod(string $path): bool
    {
        foreach ($this->routes as $routes) {
            foreach ($routes as $route) {
                if ($this->matchPath($route->path, $path) !== null) {
                    return true;
                }
            }
        }

        return false;
    }

    private function matchPath(string $routePath, string $requestPath): ?array
    {
        $routeSegments = $this->pathSegments($routePath);
        $requestSegments = $this->pathSegments($requestPath);

        if (count($routeSegments) !== count($requestSegments)) {
            return null;
        }

        $params = [];

        foreach ($routeSegments as $i => $routeSegment) {
            $requestSegment = $requestSegments[$i];

            if (preg_match('/^\{([A-Za-z_][A-Za-z0-9_]*)}$/', $routeSegment, $matches)) {
                $params[$matches[1]] = urldecode($requestSegment);
                continue;
            }

            if ($routeSegment !== $requestSegment) {
                return null;
            }
        }

        return $params;
    }

    private function pathSegments(string $path): array
    {
        $trimmedPath = trim($path, '/');

        if ($trimmedPath === '') {
            return [];
        }

        return explode('/', $trimmedPath);
    }
}
