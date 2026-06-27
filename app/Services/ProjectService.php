<?php

namespace App\Services;

use App\Contracts\Services\ProjectServiceInterface;
use App\Contracts\Repositories\ProjectRepositoryInterface;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Access\AuthorizationException;

class ProjectService implements ProjectServiceInterface
{
    protected $projectRepo;

    public function __construct(ProjectRepositoryInterface $projectRepo)
    {
        $this->projectRepo = $projectRepo;
    }

    public function createProject(array $data, User $user): Project
    {
        return DB::transaction(function () use ($data, $user) {
            // 1. Create Project
            $project = $this->projectRepo->create([
                'workspace_id' => $data['workspace_id'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'created_by' => $user->id,
            ]);

            // 2. Assign creator as Project Leader
            $this->projectRepo->addMember($project->id, $user->id, 'leader');

            // 3. Create default Kanban statuses
            $defaultStatuses = [
                ['name' => 'To Do', 'color_hex' => '#9E9E9E', 'position' => 1],
                ['name' => 'In Progress', 'color_hex' => '#2196F3', 'position' => 2],
                ['name' => 'Done', 'color_hex' => '#4CAF50', 'position' => 3],
            ];

            foreach ($defaultStatuses as $status) {
                TaskStatus::create([
                    'project_id' => $project->id,
                    'name' => $status['name'],
                    'color_hex' => $status['color_hex'],
                    'position' => $status['position'],
                ]);
            }

            return $project;
        });
    }

    public function addProjectMember(string $projectId, array $memberData, User $currentUser): bool
    {
        $project = $this->projectRepo->findById($projectId);
        if (!$project) {
            return false;
        }

        $workspace = $project->workspace;

        // Cek otorisasi
        $isWorkspaceOwner = $workspace->owner_id === $currentUser->id;
        $projectRole = ProjectMember::where('project_id', $projectId)
            ->where('user_id', $currentUser->id)
            ->first();

        $isProjectLeader = $projectRole && in_array($projectRole->role_project, ['leader', 'co_leader']);

        if (!$isWorkspaceOwner && !$isProjectLeader) {
            throw new AuthorizationException("Anda tidak memiliki hak akses untuk menambah anggota proyek.");
        }

        // Cek apakah user yang ditambahkan adalah anggota workspace
        $isWorkspaceMember = $workspace->members()->where('user_id', $memberData['user_id'])->exists() || $workspace->owner_id === $memberData['user_id'];
        if (!$isWorkspaceMember) {
            throw new \InvalidArgumentException("User yang dipilih harus merupakan anggota workspace ini.");
        }

        return $this->projectRepo->addMember($projectId, $memberData['user_id'], $memberData['role']);
    }
}
