<?php

use Slim\App;

use App\Controllers\ProgrammeController;
use App\Controllers\InterestController;
use App\Controllers\AuthController;
use App\Middleware\AuthMiddleware;

return function (App $app) {


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

    // Create programme (Admin only)
    $app->post('/programmes', [$programmeController, 'createProgramme'])
        ->add(new AuthMiddleware());
 
    // Update programme (Admin only)
    $app->put('/programmes/{id}', [$programmeController, 'updateProgramme'])
        ->add(new AuthMiddleware());

    // Delete programme (Admin only)
    $app->delete('/programmes/{id}', [$programmeController, 'deleteProgramme'])
        ->add(new AuthMiddleware());
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

        /*
    |--------------------------------------------------------------------------
    | PROTECTED ADMIN ROUTE
    |--------------------------------------------------------------------------
    */

    $app->get('/admin/dashboard', function ($request, $response) {

        $user = $request->getAttribute('user');

        $response->getBody()->write(json_encode([
            'status' => true,
            'message' => 'Welcome Admin',
            'user' => $user
        ]));

        return $response
            ->withHeader('Content-Type', 'application/json');

    })->add(new AuthMiddleware());
};