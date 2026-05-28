<?php
// This file is part of Student Course Hub.
// This Twig extension provides a csrf_token() function for templates.
// It generates and stores a secure CSRF token in the session if one does not
// already exist, allowing forms to include a token for basic CSRF protection.

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CsrfExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('csrf_token', [$this, 'getCsrfToken']),
        ];
    }

    public function getCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}
