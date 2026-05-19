<?php

use Slim\App;

use App\Controllers\ProgrammeController;
use App\Controllers\InterestController;
use App\Controllers\AuthController;

return function (App $app) {

 $app->get('/test-login', function ($request, $response) {

        $response->getBody()->write("LOGIN ROUTE FILE WORKING");

        return $response;
    });

    /*
    |--------------------------------------------------------------------------
    | CONTROLLER OBJECTS
    |--------------------------------------------------------------------------
    */

    $programmeController = new ProgrammeController();

    $interestController = new InterestController();

    $authController = new AuthController();

    /*
    |--------------------------------------------------------------------------
    | PROGRAMME ROUTES
    |--------------------------------------------------------------------------
    */

    // Get all programmes
    $app->get('/programmes', [$programmeController, 'getAllProgrammes']);

    // Get single programme
    $app->get('/programmes/{id}', [$programmeController, 'getProgramme']);

    // Search programmes
    $app->get('/search', [$programmeController, 'searchProgrammes']);

    /*
    |--------------------------------------------------------------------------
    | INTEREST ROUTES
    |--------------------------------------------------------------------------
    */

    // Register interest
    $app->post('/interest', [$interestController, 'registerInterest']);

    // Remove interest
    $app->delete('/interest/{id}', [$interestController, 'removeInterest']);

    /*
    |--------------------------------------------------------------------------
    | AUTH ROUTES
    |--------------------------------------------------------------------------
    */

    // Admin login
    $app->post('/login', [$authController, 'login']);

};