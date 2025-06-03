<?php

namespace Milos\Dentists\Core;

use Milos\Dentists\Core\Exception\APIException;
use Milos\Dentists\Core\Response\JSONResponse;

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

        $res = null;
        try {
            $class = new $class();
            $res = call_user_func_array([$class, $controller], ['req' => $this->req]);
        }
        catch (\PDOException $e) {
            $res = new JSONResponse([
                'status' => 'fail',
                'message' => 'something went wrong when connecting to the database!',
                'details' => $e->getMessage()
            ], 500);
        }
        catch (APIException $e) {
            if ($e->extraInfo) {
                $res = new JSONResponse([
                    'status' => 'fail',
                    'message' => $e->getMessage(),
                    'errors' => $e->extraInfo
                ], $e->statusCode);
            }
            else {
                $res = new JSONResponse([
                    'status' => 'fail',
                    'message' => $e->getMessage(),
                ], $e->statusCode);
            }
        }
        finally {
            echo $res->send();
        }
    }
}