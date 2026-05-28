<?php

// This middleware protects admin routes by ensuring a user is logged in with an admin role.
// It redirects unauthenticated users to the login page, remembers the intended URL,
// and prevents staff‑level accounts from accessing admin‑only areas by redirecting them
// to the staff dashboard instead.

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class AuthMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (empty($_SESSION['admin_id'])) {
            $_SESSION['flash']['error'] = 'Please log in to access the admin area.';
            $_SESSION['intended_url'] = (string) $request->getUri();

            $response = new Response();
            return $response
                ->withHeader('Location', '/auth/login')
                ->withStatus(302);
        }

        // Staff role cannot access admin area — redirect to staff dashboard
        if (($_SESSION['admin_role'] ?? '') === 'staff') {
            $_SESSION['flash']['error'] = 'You do not have permission to access the admin area.';
            $response = new Response();
            return $response->withHeader('Location', '/staff/dashboard')->withStatus(302);
        }

        return $handler->handle($request);
    }
}
