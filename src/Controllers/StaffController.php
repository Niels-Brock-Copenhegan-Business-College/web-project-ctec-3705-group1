<?php

// This controller manages all staff-facing functionality in the Student Course Hub.
// It displays the staff dashboard, shows modules and programmes led or taught by
// the logged‑in staff member, and links admin accounts to staff profiles using
// email matching. It also provides helper logic for retrieving the associated
// staff record for the current session.

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Database;
use App\Models\Module;
use App\Models\Programme;
use App\Models\Staff;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class StaffController
{
    public function __construct(
        private Twig $view,
        private Database $db
    ) {}

    // ── Dashboard ─────────────────────────────────────────────────────────────

    public function dashboard(Request $request, Response $response): Response
    {
        $staffMember = $this->getStaffMember();

        // If logged in as admin (not a staff record), show generic view
        if (!$staffMember) {
            return $this->view->render($response, 'staff/dashboard.twig', [
                'staff_member' => null,
                'modules'      => collect(),
                'programmes'   => collect(),
            ]);
        }

        $modules    = Module::where('leader_id', $staffMember->id)->get();
        $programmes = Programme::where('leader_id', $staffMember->id)->get();

        return $this->view->render($response, 'staff/dashboard.twig', [
            'staff_member' => $staffMember,
            'modules'      => $modules,
            'programmes'   => $programmes,
        ]);
    }

    // ── My Modules ────────────────────────────────────────────────────────────

    public function myModules(Request $request, Response $response): Response
    {
        $staffMember = $this->getStaffMember();

        if (!$staffMember) {
            $_SESSION['flash']['error'] = 'No staff profile linked to your account.';
            return $response->withHeader('Location', '/staff/dashboard')->withStatus(302);
        }

        $modules = Module::where('leader_id', $staffMember->id)
            ->with('programmeModules.programme')
            ->get();

        return $this->view->render($response, 'staff/my-modules.twig', [
            'staff_member' => $staffMember,
            'modules'      => $modules,
        ]);
    }

    // ── My Programmes ─────────────────────────────────────────────────────────

    public function myProgrammes(Request $request, Response $response): Response
    {
        $staffMember = $this->getStaffMember();

        if (!$staffMember) {
            $_SESSION['flash']['error'] = 'No staff profile linked to your account.';
            return $response->withHeader('Location', '/staff/dashboard')->withStatus(302);
        }

        // Programmes they lead directly
        $ledProgrammes = Programme::where('leader_id', $staffMember->id)
            ->with('programmeModules.module')
            ->get();

        // Programmes that contain their modules
        $moduleIds = Module::where('leader_id', $staffMember->id)->pluck('id');

        $involvedProgrammes = Programme::whereHas('programmeModules', function ($q) use ($moduleIds) {
            $q->whereIn('module_id', $moduleIds);
        })
        ->where('leader_id', '!=', $staffMember->id) // exclude ones already in ledProgrammes
        ->with('leader')
        ->get();

        return $this->view->render($response, 'staff/my-programmes.twig', [
            'staff_member'        => $staffMember,
            'led_programmes'      => $ledProgrammes,
            'involved_programmes' => $involvedProgrammes,
        ]);
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    /**
     * Find the Staff record linked to the currently logged-in admin account
     * by matching email addresses.
     */
    private function getStaffMember(): ?Staff
    {
        if (empty($_SESSION['admin_id'])) {
            return null;
        }

        // Match staff record by email to the logged-in admin account
        $admin = \App\Models\Admin::find($_SESSION['admin_id']);
        if (!$admin) return null;

        return Staff::where('email', $admin->email)->first();
    }
}
