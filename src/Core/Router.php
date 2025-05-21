<?php

namespace Milos\Dentists\Core;

class Router
{
    private array $routes = [];
    private Request $req;

    public function __construct(Request $req)
    {
        $this->req = $req;
    }

    private function registerRoute(string $route, string $method, array $action): void
    {
        $this->routes[$method][$route] = $action;
    }

    public function registerRoutes(array $controllers): void
    {
        foreach ($controllers as $controller) {
            $reflectionController = new \ReflectionClass($controller);

            foreach ($reflectionController->getMethods() as $method) {
                $attributes = $method->getAttributes(Route::class);

                foreach ($attributes as $attribute) {
                    $route = $attribute->newInstance();
                    $this->registerRoute($route->path, $route->method, [$controller, $method->getName()]);
                }
            }
        }
    }

    public function resolve(): void
    {
        $method = $this->req->getMethod();
        $path = $this->req->getPath();
        [$class, $controller] = $this->routes[$method][$path];
        $class = new $class();

        echo call_user_func_array([$class, $controller], ['req' => $this->req]);
    }
}