<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class InterestController
{
    /*
    |--------------------------------------------------------------------------
    | REGISTER INTEREST
    |--------------------------------------------------------------------------
    */

    public function registerInterest(Request $request, Response $response)
    {
        $data = $request->getParsedBody();

        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        if (
            empty($data['name']) ||
            empty($data['email']) ||
            empty($data['programme_id'])
        ) {

            $response->getBody()->write(json_encode([
                'status' => false,
                'message' => 'All fields are required'
            ]));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

        /*
        |--------------------------------------------------------------------------
        | EMAIL VALIDATION
        |--------------------------------------------------------------------------
        */

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {

            $response->getBody()->write(json_encode([
                'status' => false,
                'message' => 'Invalid email address'
            ]));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

        /*
        |--------------------------------------------------------------------------
        | SUCCESS RESPONSE
        |--------------------------------------------------------------------------
        */

        $response->getBody()->write(json_encode([
            'status' => true,
            'message' => 'Interest registered successfully',
            'student' => [
                'name' => $data['name'],
                'email' => $data['email']
            ],
            'programme_id' => $data['programme_id']
        ]));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201);
    }

    /*
    |--------------------------------------------------------------------------
    | REMOVE INTEREST
    |--------------------------------------------------------------------------
    */

    public function removeInterest(Request $request, Response $response, $args)
    {
        $id = $args['id'];

        $response->getBody()->write(json_encode([
            'status' => true,
            'message' => 'Interest removed successfully',
            'interest_id' => $id
        ]));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}