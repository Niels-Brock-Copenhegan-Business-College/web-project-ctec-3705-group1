<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Admin;
use App\Models\Database;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class AuthController
{
    public function __construct(
        private Twig $view,
        private Database $db
    ) {}

    public function loginForm(Request $request, Response $response): Response
    {
        if (!empty($_SESSION['admin_id'])) {
            return $response->withHeader('Location', '/admin')->withStatus(302);
        }

        return $this->view->render($response, 'auth/login.twig');
    }

    public function login(Request $request, Response $response): Response
    {
        $data  = $request->getParsedBody();
        $email = trim($data['email'] ?? '');
        $pass  = $data['password'] ?? '';

        // CSRF-lite: token check (simple session token)
        if (empty($data['_token']) || $data['_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['flash']['error'] = 'Invalid request. Please try again.';
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $admin = Admin::where('email', $email)->first();

        if (!$admin || !$admin->verifyPassword($pass)) {
            $_SESSION['flash']['error'] = 'Invalid email or password.';
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        session_regenerate_id(true);
        $_SESSION['admin_id']   = $admin->id;
        $_SESSION['admin_name'] = $admin->name;
        $_SESSION['admin_role'] = $admin->role;

        $redirect = $_SESSION['intended_url'] ?? '/admin';
        unset($_SESSION['intended_url']);

        return $response->withHeader('Location', $redirect)->withStatus(302);
    }

    public function logout(Request $request, Response $response): Response
    {
        session_destroy();
        return $response->withHeader('Location', '/auth/login')->withStatus(302);
    }
}
