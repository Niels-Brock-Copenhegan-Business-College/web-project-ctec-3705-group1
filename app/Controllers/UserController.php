<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserController
{
    public function getUsers(Request $request, Response $response)
    {
        $data = [
            [
                'id' => 1,
                'name' => 'John'
            ]
        ];

        $response->getBody()->write(json_encode([
            'status' => true,
            'data' => $data
        ]));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    public function createUser(Request $request, Response $response)
    {
        $data = $request->getParsedBody();

        if (empty($data['name'])) {

            $response->getBody()->write(json_encode([
                'status' => false,
                'message' => 'Name is required'
            ]));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

        $response->getBody()->write(json_encode([
            'status' => true,
            'message' => 'User created successfully'
        ]));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201);
    }

    public function updateUser(Request $request, Response $response, $args)
    {
        $id = $args['id'];

        $response->getBody()->write(json_encode([
            'status' => true,
            'message' => 'User updated',
            'id' => $id
        ]));

        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function deleteUser(Request $request, Response $response, $args)
    {
        $id = $args['id'];

        $response->getBody()->write(json_encode([
            'status' => true,
            'message' => 'User deleted',
            'id' => $id
        ]));

        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}