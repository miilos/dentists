<?php

use Milos\Dentists\Controller\AppointmentController;
use Milos\Dentists\Controller\DentistController;
use Milos\Dentists\Controller\ServicesController;
use Milos\Dentists\Core\Router;
use Milos\Dentists\Core\Request;
use Milos\Dentists\Controller\AuthController;

require __DIR__ . '/../vendor/autoload.php';

Dotenv\Dotenv::createImmutable(__DIR__.'/../')->load();

const ROOT_PATH = __DIR__.'/../';

$router = new Router(new Request());

$router->registerRoutes([
    AuthController::class,
    DentistController::class,
    AppointmentController::class,
    ServicesController::class,
]);

$router->registerMiddleware([
    DentistController::class,
    AppointmentController::class,
    AuthController::class,
    ServicesController::class,
]);

$router->resolve();