<?php

// This middleware protects staff‑only routes by ensuring the user is logged in
// and has either a staff or admin role. It redirects unauthenticated users to
// the login page, remembers the intended URL, and blocks users without the
// required role from accessing staff‑restricted areas.

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class StaffMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Must be logged in
        if (empty($_SESSION['admin_id'])) {
            $_SESSION['flash']['error'] = 'Please log in to access this area.';
            $_SESSION['intended_url'] = (string) $request->getUri();

            $response = new Response();
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        // Must be staff or admin role
        if (!in_array($_SESSION['admin_role'] ?? '', ['staff', 'admin'])) {
            $response = new Response();
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        return $handler->handle($request);
    }
}
