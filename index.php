<?php

use Milos\Dentists\Core\Router;
use Milos\Dentists\Core\Request;
use Milos\Dentists\Controller\AuthController;

require __DIR__ . '/vendor/autoload.php';

Dotenv\Dotenv::createImmutable(__DIR__)->load();

$router = new Router(new Request());

$router->registerRoutes([
    AuthController::class
]);

$router->resolve();