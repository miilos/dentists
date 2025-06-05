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

    private function resolveParams(string $method, string $path): array
    {
        $requestedRoute = '/' . trim($path, '/') ?? '/';
        $routes = $this->routes[$method];
        $routeParams = [];
        $definedRoute = '';

        foreach ($routes as $route => $action) {
            // convert route to regex
            // /jobs/{id} will be transformed into /jobs/@^(regex for letters, numbers and characters)$@
            // if the route contains type specifications, like \d+ for numbers only,
            // it will be transformed from /jobs/{id:\d+} to /jobs/@^(\d+)$@
            $routeRegex = preg_replace_callback('/{\w+(:([^}]+))?}/', function ($matches) {
                return isset ($matches[1]) ? '(' . $matches[2] . ')' : '([a-zA-Z0-9_-]+)';
            }, $route);
            $routeRegex = '@^' . $routeRegex . '$@';

            // check if current route matches the regex
            if (preg_match($routeRegex, $requestedRoute, $matches)) {
                // matches[0] is the full match, only the values of the params are needed
                // they're in the rest of the array, because preg_match stores each separate match
                // (part of the string that matches the part of the regex enclosed in ())
                // in a separate array element
                array_shift($matches);
                $routeParamVals = $matches;

                // because (\w+) is enclosed in brackets, the part of the url before the dynamic parameter
                // will match that part of the regex and be places in matches[1]
                $routeParamNames = [];
                if (preg_match_all('/{(\w+)(:[^}]+)?}/', $route, $matches))
                {
                    $routeParamNames = $matches[1];
                }

                // get route as it's written in the routing function
                // so that the appropriate function can be found in the routes array later
                $definedRoute = $route;
                // combine route names and values into an associative array to add to request
                $routeParams = array_combine($routeParamNames, $routeParamVals);
            }
        }

        // [ route as defined in the router, route params ]
        return [$definedRoute, $routeParams];
    }

    public function resolve(): void
    {
        try {
            $method = $this->req->getMethod();
            $path = $this->req->getPath();

            $params = $this->resolveParams($method, $path);
            $this->req->params = $params[1];

            // if the route has a parameter, like {id}, $path doesn't contain the placeholder, but the value
            // in order for the controller to be fetched from the $routes array, $params[0] has to be
            // used, because it contains the parameter placeholder

            [$class, $controller] = $this->routes[$method][$params[0]];
            $res = null;

            if (class_exists($class) && method_exists($class, $controller)) {
                $class = new $class();
                $res = call_user_func_array([$class, $controller], ['req' => $this->req]);
            }
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
            if (null === $res) {
                $res = new JSONResponse([
                    'status' => 'fail',
                    'message' => 'route not defined!',
                ], 404);
            }

            echo $res->send();
        }
    }
}