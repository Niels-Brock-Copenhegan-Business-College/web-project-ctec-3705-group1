<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProgrammeController
{
    /*
    |--------------------------------------------------------------------------
    | GET ALL PROGRAMMES
    |--------------------------------------------------------------------------
    */

    public function getAllProgrammes(Request $request, Response $response)
    {
        $programmes = [

            [
                'id' => 1,
                'title' => 'BSc Cyber Security',
                'level' => 'Undergraduate',
                'published' => true
            ],

            [
                'id' => 2,
                'title' => 'MSc Data Science',
                'level' => 'Postgraduate',
                'published' => true
            ]
        ];

        $response->getBody()->write(json_encode([
            'status' => true,
            'data' => $programmes
        ]));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    /*
    |--------------------------------------------------------------------------
    | GET SINGLE PROGRAMME
    |--------------------------------------------------------------------------
    */

    public function getProgramme(Request $request, Response $response, $args)
    {
        $id = $args['id'];

        $programme = [
            'id' => $id,
            'title' => 'BSc Cyber Security',
            'description' => 'Cyber Security degree programme',
            'modules' => [
                'Ethical Hacking',
                'Digital Forensics',
                'Network Security'
            ]
        ];

        $response->getBody()->write(json_encode([
            'status' => true,
            'data' => $programme
        ]));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    /*
    |--------------------------------------------------------------------------
    | SEARCH PROGRAMMES
    |--------------------------------------------------------------------------
    */

    public function searchProgrammes(Request $request, Response $response)
    {
        $params = $request->getQueryParams();

        $keyword = $params['keyword'] ?? '';

        $response->getBody()->write(json_encode([
            'status' => true,
            'search_keyword' => $keyword
        ]));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
        /*
    |--------------------------------------------------------------------------
    | CREATE PROGRAMME
    |--------------------------------------------------------------------------
    */

    public function createProgramme(Request $request, Response $response)
    {
        $data = $request->getParsedBody();

        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        if (
            empty($data['title']) ||
            empty($data['department']) ||
            empty($data['duration'])
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
        | SUCCESS RESPONSE
        |--------------------------------------------------------------------------
        */

        $response->getBody()->write(json_encode([
            'status' => true,
            'message' => 'Programme created successfully',
            'programme' => [
                'title' => $data['title'],
                'department' => $data['department'],
                'duration' => $data['duration']
            ]
        ]));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201);
    }

        /*
    |--------------------------------------------------------------------------
    | UPDATE PROGRAMME
    |--------------------------------------------------------------------------
    */

    public function updateProgramme(Request $request, Response $response, $args)
    {
        $programmeId = $args['id'];

        $data = $request->getParsedBody();

        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        if (
            empty($data['title']) ||
            empty($data['department']) ||
            empty($data['duration'])
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
        | SUCCESS RESPONSE
        |--------------------------------------------------------------------------
        */

        $response->getBody()->write(json_encode([
            'status' => true,
            'message' => 'Programme updated successfully',
            'programme_id' => $programmeId,
            'updated_data' => [
                'title' => $data['title'],
                'department' => $data['department'],
                'duration' => $data['duration']
            ]
        ]));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}