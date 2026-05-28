<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Database;
use App\Models\Programme;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class StudentController
{
    public function __construct(
        private Twig $view,
        private Database $db
    ) {}

    public function home(Request $request, Response $response): Response
    {
        $featured = Programme::published()
            ->with('leader')
            ->limit(6)
            ->get();

        $ugCount = Programme::published()->level('Undergraduate')->count();
        $pgCount = Programme::published()->level('Postgraduate')->count();

        return $this->view->render($response, 'student/home.twig', [
            'featured'  => $featured,
            'ug_count'  => $ugCount,
            'pg_count'  => $pgCount,
        ]);
    }

    public function programmes(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $level  = $params['level'] ?? '';
        $search = trim($params['search'] ?? '');

        $query = Programme::published()->with('leader');

        if ($level && in_array($level, ['Undergraduate', 'Postgraduate'])) {
            $query->level($level);
        }

        if ($search) {
            $query->search($search);
        }

        $programmes = $query->orderBy('title')->get();

        return $this->view->render($response, 'student/programmes.twig', [
            'programmes' => $programmes,
            'level'      => $level,
            'search'     => $search,
        ]);
    }

    public function programme(Request $request, Response $response, array $args): Response
    {
        $programme = Programme::published()
            ->where('slug', $args['slug'])
            ->with(['leader', 'programmeModules.module.leader'])
            ->firstOrFail();

        // Group modules by year
        $modulesByYear = [];
        foreach ($programme->programmeModules as $pm) {
            $modulesByYear[$pm->year][] = $pm->module;
        }
        ksort($modulesByYear);

        // Check if current visitor already registered interest
        $alreadyRegistered = false;
        if (!empty($_SESSION['interest_email'])) {
            $alreadyRegistered = $programme->interests()
                ->where('email', $_SESSION['interest_email'])
                ->exists();
        }

        return $this->view->render($response, 'student/programme.twig', [
            'programme'         => $programme,
            'modules_by_year'   => $modulesByYear,
            'already_registered' => $alreadyRegistered,
        ]);
    }
}
