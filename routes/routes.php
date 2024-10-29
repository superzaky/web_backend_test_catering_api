<?php

/** @var Bramus\Router\Router $router */

// Define routes here
$router->get('/test', App\Controllers\IndexController::class . '@test');
$router->get('/', App\Controllers\IndexController::class . '@test');
$router->post('/facility', App\Controllers\IndexController::class . '@create');
$router->get('/facility', App\Controllers\IndexController::class . '@readMultiple');
$router->get('/facility/(\d+)', App\Controllers\IndexController::class . '@read');
$router->put('/facility/(\d+)', App\Controllers\IndexController::class . '@update');
$router->delete('/facility/(\d+)', App\Controllers\IndexController::class . '@delete');
