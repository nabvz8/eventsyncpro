<?php

namespace App\Http\Controllers;

use App\Contracts\Services\ProjectServiceInterface;
use App\Contracts\Repositories\ProjectRepositoryInterface;
use App\Contracts\Services\TaskServiceInterface;
use App\Models\Workspace;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Auth\Access\AuthorizationException;

class ProjectController extends Controller
{
    protected $projectService;
    protected $projectRepo;
    protected $taskService;

    public function __construct(
        ProjectServiceInterface $projectService,
        ProjectRepositoryInterface $projectRepo,
        TaskServiceInterface $taskService
    ) {
        $this->projectService = $projectService;
        $this->projectRepo = $projectRepo;
        $this->taskService = $taskService;
    }

    /**
     * Membuat proyek baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $activeWorkspaceId = session('active_workspace_id');
        if (!$activeWorkspaceId) {
            return back()->withErrors(['workspace' => 'Silakan pilih workspace aktif terlebih dahulu.']);
        }

        $workspace = Workspace::find($activeWorkspaceId);
        // Pastikan user memiliki akses ke workspace
        $user = $request->user();
        $hasAccess = $workspace->owner_id === $user->id || $workspace->members()->where('user_id', $user->id)->exists();
        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses ke workspace ini.');
        }

        $validated['workspace_id'] = $activeWorkspaceId;

        $this->projectService->createProject($validated, $user);

        return back();
    }

    /**
     * Tampilkan detail proyek.
     */
    public function show(Request $request, string $id): Response
    {
        $project = $this->projectRepo->findById($id);
        if (!$project) {
            abort(404, 'Proyek tidak ditemukan.');
        }

        $user = $request->user();
        $workspace = $project->workspace;

        // Cek otorisasi untuk mengakses proyek (harus anggota workspace)
        $isWorkspaceMember = $workspace->owner_id === $user->id || $workspace->members()->where('user_id', $user->id)->exists();
        if (!$isWorkspaceMember) {
            abort(403, 'Anda tidak memiliki akses ke proyek ini.');
        }

        // Dapatkan semua member di workspace untuk opsi dropdown tambah member proyek
        $workspaceMembers = User::where('id', $workspace->owner_id)
            ->orWhereHas('joinedWorkspaces', function ($query) use ($workspace) {
                $query->where('workspace_id', $workspace->id);
            })->get();

        // Cari tahu role user di proyek ini
        $myProjectMember = $project->members()->where('user_id', $user->id)->first();
        $myRole = $myProjectMember ? $myProjectMember->pivot->role_project : ($workspace->owner_id === $user->id ? 'leader' : null);

        // Ambil board data Kanban (status + tasks, filtered by permission)
        $boardData = $this->taskService->getBoardData($project->id, $user);

        return Inertia::render('Projects/Show', [
            'project' => $project,
            'workspaceMembers' => $workspaceMembers,
            'myRole' => $myRole,
            'boardData' => $boardData,
        ]);
    }

    /**
     * Menambahkan anggota ke proyek.
     */
    public function addMember(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
            'role' => 'required|string|in:leader,co_leader',
        ]);

        $this->projectService->addProjectMember($id, $validated, $request->user());

        return back();
    }
}
