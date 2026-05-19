<?php

use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->get('/users', function ($request, $response) {

    $response->getBody()->write("USERS ROUTE WORKING");

    return $response;
});

$app->run();