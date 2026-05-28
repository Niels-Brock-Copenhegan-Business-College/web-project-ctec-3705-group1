<?php

declare(strict_types=1);

use Slim\App;
use Slim\Views\TwigMiddleware;
use App\Controllers\StudentController;
use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\InterestController;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;

return function (App $app) {

    // Add Twig middleware
    $app->add(TwigMiddleware::createFromContainer($app, \Slim\Views\Twig::class));

    // ── Public / Student Routes ──────────────────────────────────────────────
    $app->get('/', [StudentController::class, 'home'])->setName('home');

    $app->get('/programmes', [StudentController::class, 'programmes'])->setName('programmes');
    $app->get('/programmes/{slug}', [StudentController::class, 'programme'])->setName('programme.show');

    // Interest registration
    $app->post('/interest/register', [InterestController::class, 'register'])->setName('interest.register');
    $app->post('/interest/withdraw', [InterestController::class, 'withdraw'])->setName('interest.withdraw');
    $app->get('/interest/manage', [InterestController::class, 'manage'])->setName('interest.manage');

    // ── Auth Routes ──────────────────────────────────────────────────────────
    $app->group('/auth', function ($group) use ($app) {
        $group->get('/login', [AuthController::class, 'loginForm'])->setName('auth.login');
        $group->post('/login', [AuthController::class, 'login']);
        $group->post('/logout', [AuthController::class, 'logout'])->setName('auth.logout');
    });

    // ── Admin Routes (protected) ─────────────────────────────────────────────
    $app->group('/admin', function ($group) {

        $group->get('', [AdminController::class, 'dashboard'])->setName('admin.dashboard');

        // Programmes
        $group->get('/programmes', [AdminController::class, 'programmes'])->setName('admin.programmes');
        $group->get('/programmes/create', [AdminController::class, 'createProgramme'])->setName('admin.programmes.create');
        $group->post('/programmes', [AdminController::class, 'storeProgramme'])->setName('admin.programmes.store');
        $group->get('/programmes/{id}/edit', [AdminController::class, 'editProgramme'])->setName('admin.programmes.edit');
        $group->post('/programmes/{id}', [AdminController::class, 'updateProgramme'])->setName('admin.programmes.update');
        $group->post('/programmes/{id}/delete', [AdminController::class, 'deleteProgramme'])->setName('admin.programmes.delete');
        $group->post('/programmes/{id}/toggle-publish', [AdminController::class, 'togglePublish'])->setName('admin.programmes.togglePublish');

        // Modules
        $group->get('/modules', [AdminController::class, 'modules'])->setName('admin.modules');
        $group->get('/modules/create', [AdminController::class, 'createModule'])->setName('admin.modules.create');
        $group->post('/modules', [AdminController::class, 'storeModule'])->setName('admin.modules.store');
        $group->get('/modules/{id}/edit', [AdminController::class, 'editModule'])->setName('admin.modules.edit');
        $group->post('/modules/{id}', [AdminController::class, 'updateModule'])->setName('admin.modules.update');
        $group->post('/modules/{id}/delete', [AdminController::class, 'deleteModule'])->setName('admin.modules.delete');

        // Staff
        $group->get('/staff', [AdminController::class, 'staff'])->setName('admin.staff');
        $group->get('/staff/create', [AdminController::class, 'createStaff'])->setName('admin.staff.create');
        $group->post('/staff', [AdminController::class, 'storeStaff'])->setName('admin.staff.store');
        $group->get('/staff/{id}/edit', [AdminController::class, 'editStaff'])->setName('admin.staff.edit');
        $group->post('/staff/{id}', [AdminController::class, 'updateStaff'])->setName('admin.staff.update');
        $group->post('/staff/{id}/delete', [AdminController::class, 'deleteStaff'])->setName('admin.staff.delete');

        // Mailing lists
        $group->get('/mailing-list', [AdminController::class, 'mailingList'])->setName('admin.mailing-list');
        $group->get('/mailing-list/export', [AdminController::class, 'exportMailingList'])->setName('admin.mailing-list.export');
        $group->post('/mailing-list/{id}/delete', [AdminController::class, 'deleteInterest'])->setName('admin.mailing-list.delete');

    })->add(new AuthMiddleware());
};
