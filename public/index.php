<?php

use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$app->addBodyParsingMiddleware();

(require __DIR__ . '/../routes/routes.php')($app);

$app->run();