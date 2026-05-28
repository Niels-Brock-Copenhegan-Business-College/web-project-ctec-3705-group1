<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Database;
use App\Models\Interest;
use App\Models\Programme;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class InterestController
{
    public function __construct(
        private Twig $view,
        private Database $db
    ) {}

    public function register(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        $programmeId = (int) ($data['programme_id'] ?? 0);
        $firstName   = trim(htmlspecialchars($data['first_name'] ?? '', ENT_QUOTES, 'UTF-8'));
        $lastName    = trim(htmlspecialchars($data['last_name'] ?? '', ENT_QUOTES, 'UTF-8'));
        $email       = strtolower(trim($data['email'] ?? ''));
        $phone       = trim(htmlspecialchars($data['phone'] ?? '', ENT_QUOTES, 'UTF-8'));

        // Validate
        $errors = [];
        if (!$firstName) $errors[] = 'First name is required.';
        if (!$lastName)  $errors[] = 'Last name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
        if (!$programmeId) $errors[] = 'Programme not found.';

        $programme = Programme::published()->find($programmeId);
        if (!$programme) $errors[] = 'Programme not found or not available.';

        // Duplicate check
        if (!$errors && Interest::where('programme_id', $programmeId)->where('email', $email)->exists()) {
            $errors[] = 'You have already registered your interest in this programme.';
        }

        if ($errors) {
            $_SESSION['flash']['error'] = implode(' ', $errors);
            $slug = $programme ? $programme->slug : '';
            return $response->withHeader('Location', "/programmes/{$slug}")->withStatus(302);
        }

        Interest::create([
            'programme_id' => $programmeId,
            'first_name'   => $firstName,
            'last_name'    => $lastName,
            'email'        => $email,
            'phone'        => $phone,
        ]);

        // Remember email in session for UX
        $_SESSION['interest_email'] = $email;

        $_SESSION['flash']['success'] = "Thank you, {$firstName}! We've registered your interest in {$programme->title}.";

        return $response->withHeader('Location', "/programmes/{$programme->slug}")->withStatus(302);
    }

    public function withdraw(Request $request, Response $response): Response
    {
        $data        = $request->getParsedBody();
        $programmeId = (int) ($data['programme_id'] ?? 0);
        $email       = strtolower(trim($data['email'] ?? ''));

        if (!$email || !$programmeId) {
            $_SESSION['flash']['error'] = 'Unable to process your request.';
            return $response->withHeader('Location', '/programmes')->withStatus(302);
        }

        $deleted = Interest::where('programme_id', $programmeId)
            ->where('email', $email)
            ->delete();

        if ($deleted) {
            $_SESSION['flash']['success'] = 'Your interest registration has been withdrawn.';
        } else {
            $_SESSION['flash']['error'] = 'No matching registration found.';
        }

        $programme = Programme::find($programmeId);
        $slug = $programme ? $programme->slug : '';

        return $response->withHeader('Location', "/programmes/{$slug}")->withStatus(302);
    }

    public function manage(Request $request, Response $response): Response
    {
        $email = $_SESSION['interest_email'] ?? '';

        $registrations = $email
            ? Interest::where('email', $email)->with('programme')->get()
            : collect();

        return $this->view->render($response, 'student/manage-interest.twig', [
            'registrations' => $registrations,
            'email'         => $email,
        ]);
    }
}
