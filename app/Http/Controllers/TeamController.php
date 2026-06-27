<?php

namespace App\Http\Controllers;

use App\Contracts\Services\TeamServiceInterface;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class TeamController extends Controller
{
    protected $teamService;

    public function __construct(TeamServiceInterface $teamService)
    {
        $this->teamService = $teamService;
    }

    /**
     * Membuat tim pelaksana proyek baru.
     */
    public function store(Request $request, string $projId): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $project = Project::find($projId);
        if (!$project) {
            abort(404, 'Proyek tidak ditemukan.');
        }

        $validated['project_id'] = $projId;

        $this->teamService->createTeam($validated, $request->user());

        return back();
    }

    /**
     * Menambahkan anggota ke tim pelaksana.
     */
    public function addMember(Request $request, string $teamId): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
            'role' => 'required|string|in:leader,co_leader,member',
        ]);

        $this->teamService->addTeamMember($teamId, $validated, $request->user());

        return back();
    }
}
