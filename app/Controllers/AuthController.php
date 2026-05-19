<?php

namespace App\Controllers;

use Firebase\JWT\JWT;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
   private $secretKey = "THIS_IS_A_VERY_LONG_SECRET_KEY_FOR_JWT_AUTHENTICATION_2026";

    public function login(Request $request, Response $response)
    {
        $data = $request->getParsedBody();

        if (
            empty($data['email']) ||
            empty($data['password'])
        ) {

            $response->getBody()->write(json_encode([
                'status' => false,
                'message' => 'Email and password are required'
            ]));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

        $demoAdmin = [
            'id' => 1,
            'email' => 'admin@university.com',
            'password' => 'admin123',
            'role' => 'admin'
        ];

        if (
            $data['email'] !== $demoAdmin['email'] ||
            $data['password'] !== $demoAdmin['password']
        ) {

            $response->getBody()->write(json_encode([
                'status' => false,
                'message' => 'Invalid credentials'
            ]));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        }

        $payload = [
            'id' => $demoAdmin['id'],
            'email' => $demoAdmin['email'],
            'role' => $demoAdmin['role'],
            'exp' => time() + 3600
        ];

        $token = JWT::encode(
            $payload,
            $this->secretKey,
            'HS256'
        );

        $response->getBody()->write(json_encode([
            'status' => true,
            'message' => 'Login successful',
            'token' => $token
        ]));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}