<?php

// This file defines all dependency injection container bindings for the application.
// It registers services such as the database connection, Twig templating engine,
// global Twig variables, custom Twig extensions, and all controllers.
// These bindings allow Slim to automatically resolve and inject dependencies.

declare(strict_types=1);

use App\Models\Database;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

// This file returns an array of service definitions for the dependency injection container.
return [

    // Database connection (Eloquent capsule)
    Database::class => function () {
        return new Database();
    },

    // Twig view
    Twig::class => function () {
        $twig = Twig::create(__DIR__ . '/../templates', [
            'cache' => $_ENV['APP_ENV'] === 'production'
                ? __DIR__ . '/../var/cache/twig'
                : false,
            'debug' => (bool)($_ENV['APP_DEBUG'] ?? false),
        ]);

        // Add global variables
        $twig->getEnvironment()->addGlobal('app_name', $_ENV['APP_NAME'] ?? 'Student Course Hub');
        $twig->getEnvironment()->addGlobal('session', $_SESSION);
        $twig->getEnvironment()->addGlobal('flash', $_SESSION['flash'] ?? []);

        // Register extensions
        $twig->getEnvironment()->addExtension(new \App\Twig\CsrfExtension());

        // Clear flash after reading
        unset($_SESSION['flash']);

        return $twig;
    },

    // Controllers
    \App\Controllers\StudentController::class => \DI\autowire(),
    \App\Controllers\AdminController::class   => \DI\autowire(),
    \App\Controllers\AuthController::class    => \DI\autowire(),
    \App\Controllers\InterestController::class => \DI\autowire(),
    \App\Controllers\StaffController::class => \DI\autowire(),
];
