<?php

namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthMiddleware
{
    private $secretKey = "THIS_IS_A_VERY_LONG_SECRET_KEY_FOR_JWT_AUTHENTICATION_2026";

    public function __invoke(Request $request, $handler): Response
    {
        /*
        |--------------------------------------------------------------------------
        | GET AUTH HEADER
        |--------------------------------------------------------------------------
        */

        $authHeader = $request->getHeaderLine('Authorization');

        /*
        |--------------------------------------------------------------------------
        | CHECK TOKEN EXISTS
        |--------------------------------------------------------------------------
        */

        if (!$authHeader) {

            $response = new \Slim\Psr7\Response();

            $response->getBody()->write(json_encode([
                'status' => false,
                'message' => 'Authorization token required'
            ]));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        }

        /*
        |--------------------------------------------------------------------------
        | EXTRACT TOKEN
        |--------------------------------------------------------------------------
        */

        $token = str_replace('Bearer ', '', $authHeader);

        try {

            /*
            |--------------------------------------------------------------------------
            | VERIFY JWT TOKEN
            |--------------------------------------------------------------------------
            */

            $decoded = JWT::decode(
                $token,
                new Key($this->secretKey, 'HS256')
            );

            /*
            |--------------------------------------------------------------------------
            | STORE USER DATA
            |--------------------------------------------------------------------------
            */

            $request = $request->withAttribute('user', $decoded);

        } catch (\Exception $e) {

            $response = new \Slim\Psr7\Response();

            $response->getBody()->write(json_encode([
                'status' => false,
                'message' => 'Invalid or expired token'
            ]));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        }

        /*
        |--------------------------------------------------------------------------
        | CONTINUE REQUEST
        |--------------------------------------------------------------------------
        */

        return $handler->handle($request);
    }
}