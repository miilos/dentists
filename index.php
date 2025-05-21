<?php

use Milos\Dentists\Core\Router;
use Milos\Dentists\Core\Request;
use Milos\Dentists\Controller\TestController;

require __DIR__ . '/vendor/autoload.php';

$router = new Router(new Request());

$router->registerRoutes([
    TestController::class
]);

$router->resolve();