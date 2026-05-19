<?php

use Slim\App;

use App\Controllers\ProgrammeController;
use App\Controllers\InterestController;

return function (App $app) {

    /*
    |--------------------------------------------------------------------------
    | CONTROLLER OBJECTS
    |--------------------------------------------------------------------------
    */

    $programmeController = new ProgrammeController();

    $interestController = new InterestController();

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
