<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Admin;
use App\Models\Database;
use App\Models\Interest;
use App\Models\Module;
use App\Models\Programme;
use App\Models\ProgrammeModule;
use App\Models\Staff;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class AdminController
{
    public function __construct(
        private Twig $view,
        private Database $db
    ) {}

    // ── Dashboard ─────────────────────────────────────────────────────────────

    public function dashboard(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'admin/dashboard.twig', [
            'stats' => [
                'programmes'   => Programme::count(),
                'published'    => Programme::published()->count(),
                'modules'      => Module::count(),
                'staff'        => Staff::count(),
                'interests'    => Interest::count(),
            ],
        ]);
    }

    // ── Programmes ────────────────────────────────────────────────────────────

    public function programmes(Request $request, Response $response): Response
    {
        $programmes = Programme::with('leader')->orderBy('title')->get();
        return $this->view->render($response, 'admin/programmes/index.twig', compact('programmes'));
    }

    public function createProgramme(Request $request, Response $response): Response
    {
        $staff = Staff::orderBy('name')->get();
        return $this->view->render($response, 'admin/programmes/form.twig', [
            'programme' => null,
            'staff'     => $staff,
        ]);
    }

    public function storeProgramme(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $files = $request->getUploadedFiles();

        [$sanitized, $errors] = $this->validateProgramme($data);

        if ($errors) {
            $_SESSION['flash']['error'] = implode('<br>', $errors);
            return $response->withHeader('Location', '/admin/programmes/create')->withStatus(302);
        }

        // Handle image upload
        $sanitized['image'] = $this->handleImageUpload($files['image'] ?? null);

        // Slug generation
        $sanitized['slug'] = $this->generateSlug($sanitized['title']);

        Programme::create($sanitized);

        $_SESSION['flash']['success'] = 'Programme created successfully.';
        return $response->withHeader('Location', '/admin/programmes')->withStatus(302);
    }

    public function editProgramme(Request $request, Response $response, array $args): Response
    {
        $programme = Programme::with(['leader', 'programmeModules.module'])->findOrFail($args['id']);
        $staff     = Staff::orderBy('name')->get();
        $modules   = Module::orderBy('title')->get();

        return $this->view->render($response, 'admin/programmes/form.twig', [
            'programme' => $programme,
            'staff'     => $staff,
            'modules'   => $modules,
        ]);
    }

    public function updateProgramme(Request $request, Response $response, array $args): Response
    {
        $programme = Programme::findOrFail($args['id']);
        $data      = $request->getParsedBody();
        $files     = $request->getUploadedFiles();

        [$sanitized, $errors] = $this->validateProgramme($data);

        if ($errors) {
            $_SESSION['flash']['error'] = implode('<br>', $errors);
            return $response->withHeader('Location', "/admin/programmes/{$args['id']}/edit")->withStatus(302);
        }

        $image = $this->handleImageUpload($files['image'] ?? null);
        if ($image) {
            $sanitized['image'] = $image;
        }

        $programme->update($sanitized);

        // Sync programme modules if provided
        if (isset($data['modules'])) {
            ProgrammeModule::where('programme_id', $programme->id)->delete();
            foreach ($data['modules'] as $i => $moduleId) {
                ProgrammeModule::create([
                    'programme_id' => $programme->id,
                    'module_id'    => (int) $moduleId,
                    'year'         => (int) ($data['module_years'][$i] ?? 1),
                    'sort_order'   => $i,
                ]);
            }
        }

        $_SESSION['flash']['success'] = 'Programme updated.';
        return $response->withHeader('Location', '/admin/programmes')->withStatus(302);
    }

    public function deleteProgramme(Request $request, Response $response, array $args): Response
    {
        $programme = Programme::findOrFail($args['id']);
        ProgrammeModule::where('programme_id', $programme->id)->delete();
        Interest::where('programme_id', $programme->id)->delete();
        $programme->delete();

        $_SESSION['flash']['success'] = 'Programme deleted.';
        return $response->withHeader('Location', '/admin/programmes')->withStatus(302);
    }

    public function togglePublish(Request $request, Response $response, array $args): Response
    {
        $programme = Programme::findOrFail($args['id']);
        $programme->update(['is_published' => !$programme->is_published]);

        $status = $programme->is_published ? 'published' : 'unpublished';
        $_SESSION['flash']['success'] = "Programme {$status}.";
        return $response->withHeader('Location', '/admin/programmes')->withStatus(302);
    }

    // ── Modules ───────────────────────────────────────────────────────────────

    public function modules(Request $request, Response $response): Response
    {
        $modules = Module::with('leader')->orderBy('title')->get();
        return $this->view->render($response, 'admin/modules/index.twig', compact('modules'));
    }

    public function createModule(Request $request, Response $response): Response
    {
        $staff = Staff::orderBy('name')->get();
        return $this->view->render($response, 'admin/modules/form.twig', [
            'module' => null,
            'staff'  => $staff,
        ]);
    }

    public function storeModule(Request $request, Response $response): Response
    {
        $data  = $request->getParsedBody();
        $files = $request->getUploadedFiles();

        [$sanitized, $errors] = $this->validateModule($data);
        if ($errors) {
            $_SESSION['flash']['error'] = implode('<br>', $errors);
            return $response->withHeader('Location', '/admin/modules/create')->withStatus(302);
        }

        $sanitized['image'] = $this->handleImageUpload($files['image'] ?? null);
        Module::create($sanitized);

        $_SESSION['flash']['success'] = 'Module created.';
        return $response->withHeader('Location', '/admin/modules')->withStatus(302);
    }

    public function editModule(Request $request, Response $response, array $args): Response
    {
        $module = Module::findOrFail($args['id']);
        $staff  = Staff::orderBy('name')->get();
        return $this->view->render($response, 'admin/modules/form.twig', compact('module', 'staff'));
    }

    public function updateModule(Request $request, Response $response, array $args): Response
    {
        $module = Module::findOrFail($args['id']);
        $data   = $request->getParsedBody();
        $files  = $request->getUploadedFiles();

        [$sanitized, $errors] = $this->validateModule($data);
        if ($errors) {
            $_SESSION['flash']['error'] = implode('<br>', $errors);
            return $response->withHeader('Location', "/admin/modules/{$args['id']}/edit")->withStatus(302);
        }

        $image = $this->handleImageUpload($files['image'] ?? null);
        if ($image) $sanitized['image'] = $image;

        $module->update($sanitized);

        $_SESSION['flash']['success'] = 'Module updated.';
        return $response->withHeader('Location', '/admin/modules')->withStatus(302);
    }

    public function deleteModule(Request $request, Response $response, array $args): Response
    {
        $module = Module::findOrFail($args['id']);
        ProgrammeModule::where('module_id', $module->id)->delete();
        $module->delete();

        $_SESSION['flash']['success'] = 'Module deleted.';
        return $response->withHeader('Location', '/admin/modules')->withStatus(302);
    }

    // ── Staff ─────────────────────────────────────────────────────────────────

    public function staff(Request $request, Response $response): Response
    {
        $staff = Staff::withCount(['ledProgrammes', 'ledModules'])->orderBy('name')->get();
        return $this->view->render($response, 'admin/staff/index.twig', compact('staff'));
    }

    public function createStaff(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'admin/staff/form.twig', ['member' => null]);
    }

    public function storeStaff(Request $request, Response $response): Response
    {
        $data  = $request->getParsedBody();
        $files = $request->getUploadedFiles();

        [$sanitized, $errors] = $this->validateStaff($data);
        if ($errors) {
            $_SESSION['flash']['error'] = implode('<br>', $errors);
            return $response->withHeader('Location', '/admin/staff/create')->withStatus(302);
        }

        $sanitized['photo'] = $this->handleImageUpload($files['photo'] ?? null);
        Staff::create($sanitized);

        $_SESSION['flash']['success'] = 'Staff member added.';
        return $response->withHeader('Location', '/admin/staff')->withStatus(302);
    }

    public function editStaff(Request $request, Response $response, array $args): Response
    {
        $member = Staff::findOrFail($args['id']);
        return $this->view->render($response, 'admin/staff/form.twig', compact('member'));
    }

    public function updateStaff(Request $request, Response $response, array $args): Response
    {
        $member = Staff::findOrFail($args['id']);
        $data   = $request->getParsedBody();
        $files  = $request->getUploadedFiles();

        [$sanitized, $errors] = $this->validateStaff($data);
        if ($errors) {
            $_SESSION['flash']['error'] = implode('<br>', $errors);
            return $response->withHeader('Location', "/admin/staff/{$args['id']}/edit")->withStatus(302);
        }

        $photo = $this->handleImageUpload($files['photo'] ?? null);
        if ($photo) $sanitized['photo'] = $photo;

        $member->update($sanitized);

        $_SESSION['flash']['success'] = 'Staff member updated.';
        return $response->withHeader('Location', '/admin/staff')->withStatus(302);
    }

    public function deleteStaff(Request $request, Response $response, array $args): Response
    {
        Staff::findOrFail($args['id'])->delete();
        $_SESSION['flash']['success'] = 'Staff member removed.';
        return $response->withHeader('Location', '/admin/staff')->withStatus(302);
    }

    // ── Mailing List ──────────────────────────────────────────────────────────

    public function mailingList(Request $request, Response $response): Response
    {
        $params      = $request->getQueryParams();
        $programmeId = (int) ($params['programme_id'] ?? 0);
        $search      = trim($params['search'] ?? '');

        $query = Interest::with('programme');

        if ($programmeId) {
            $query->where('programme_id', $programmeId);
        }
        if ($search) {
            $query->search($search);
        }

        $interests   = $query->orderBy('created_at', 'desc')->get();
        $programmes  = Programme::orderBy('title')->get();

        return $this->view->render($response, 'admin/mailing-list/index.twig', [
            'interests'    => $interests,
            'programmes'   => $programmes,
            'programme_id' => $programmeId,
            'search'       => $search,
        ]);
    }

    public function exportMailingList(Request $request, Response $response): Response
    {
        $params      = $request->getQueryParams();
        $programmeId = (int) ($params['programme_id'] ?? 0);

        $query = Interest::with('programme');
        if ($programmeId) {
            $query->where('programme_id', $programmeId);
        }

        $interests = $query->orderBy('last_name')->get();

        $csv = "First Name,Last Name,Email,Phone,Programme,Registered At\n";
        foreach ($interests as $interest) {
            $csv .= implode(',', [
                '"' . $interest->first_name . '"',
                '"' . $interest->last_name . '"',
                '"' . $interest->email . '"',
                '"' . ($interest->phone ?? '') . '"',
                '"' . ($interest->programme->title ?? '') . '"',
                '"' . $interest->created_at . '"',
            ]) . "\n";
        }

        $response->getBody()->write($csv);

        return $response
            ->withHeader('Content-Type', 'text/csv')
            ->withHeader('Content-Disposition', 'attachment; filename="mailing-list.csv"');
    }

    public function deleteInterest(Request $request, Response $response, array $args): Response
    {
        Interest::findOrFail($args['id'])->delete();
        $_SESSION['flash']['success'] = 'Interest registration removed.';
        return $response->withHeader('Location', '/admin/mailing-list')->withStatus(302);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function validateProgramme(array $data): array
    {
        $errors    = [];
        $sanitized = [];

        $sanitized['title']          = trim(htmlspecialchars($data['title'] ?? '', ENT_QUOTES, 'UTF-8'));
        $sanitized['level']          = in_array($data['level'] ?? '', ['Undergraduate', 'Postgraduate'])
                                       ? $data['level'] : '';
        $sanitized['description']    = trim(htmlspecialchars($data['description'] ?? '', ENT_QUOTES, 'UTF-8'));
        $sanitized['duration_years'] = max(1, min(6, (int) ($data['duration_years'] ?? 3)));
        $sanitized['ucas_code']      = trim(htmlspecialchars($data['ucas_code'] ?? '', ENT_QUOTES, 'UTF-8'));
        $sanitized['leader_id']      = !empty($data['leader_id']) ? (int) $data['leader_id'] : null;
        $sanitized['is_published']   = isset($data['is_published']) ? true : false;

        if (!$sanitized['title'])   $errors[] = 'Title is required.';
        if (!$sanitized['level'])   $errors[] = 'Level must be Undergraduate or Postgraduate.';
        if (!$sanitized['description']) $errors[] = 'Description is required.';

        return [$sanitized, $errors];
    }

    private function validateModule(array $data): array
    {
        $errors    = [];
        $sanitized = [];

        $sanitized['title']       = trim(htmlspecialchars($data['title'] ?? '', ENT_QUOTES, 'UTF-8'));
        $sanitized['code']        = trim(htmlspecialchars($data['code'] ?? '', ENT_QUOTES, 'UTF-8'));
        $sanitized['description'] = trim(htmlspecialchars($data['description'] ?? '', ENT_QUOTES, 'UTF-8'));
        $sanitized['credits']     = max(0, (int) ($data['credits'] ?? 20));
        $sanitized['leader_id']   = !empty($data['leader_id']) ? (int) $data['leader_id'] : null;

        if (!$sanitized['title']) $errors[] = 'Title is required.';
        if (!$sanitized['code'])  $errors[] = 'Module code is required.';

        return [$sanitized, $errors];
    }

    private function validateStaff(array $data): array
    {
        $errors    = [];
        $sanitized = [];

        $sanitized['name']  = trim(htmlspecialchars($data['name'] ?? '', ENT_QUOTES, 'UTF-8'));
        $sanitized['email'] = strtolower(trim($data['email'] ?? ''));
        $sanitized['title'] = trim(htmlspecialchars($data['title'] ?? '', ENT_QUOTES, 'UTF-8'));
        $sanitized['bio']   = trim(htmlspecialchars($data['bio'] ?? '', ENT_QUOTES, 'UTF-8'));

        if (!$sanitized['name'])  $errors[] = 'Name is required.';
        if (!filter_var($sanitized['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';

        return [$sanitized, $errors];
    }

    private function handleImageUpload(?object $file): ?string
    {
        if (!$file || $file->getError() !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowed   = ['image/jpeg', 'image/png', 'image/webp'];
        $mediaType = $file->getClientMediaType();

        if (!in_array($mediaType, $allowed)) {
            return null;
        }

        $ext      = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
        $filename = bin2hex(random_bytes(16)) . '.' . strtolower($ext);
        $uploadDir = __DIR__ . '/../../public/uploads/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $file->moveTo($uploadDir . $filename);

        return '/uploads/' . $filename;
    }

    private function generateSlug(string $title): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
        $base = $slug;
        $i    = 1;

        while (Programme::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
