<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;

class LoggingMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $log = date('Y-m-d H:i:s') .
            ' ' .
            $request->getMethod() .
            ' ' .
            $request->getUri() .
            PHP_EOL;

        file_put_contents(
            __DIR__ . '/../../logs/app.log',
            $log,
            FILE_APPEND
        );

        return $handler->handle($request);
    }
}