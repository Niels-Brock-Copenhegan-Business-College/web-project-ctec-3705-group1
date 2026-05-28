<?php

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

        return $handler->handle($request);
    }
}
