<?php

namespace App\Services;

use App\Contracts\Services\OnboardingServiceInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Repositories\WorkspaceRepositoryInterface;
use App\Models\User;
use App\Models\Workspace;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\TaskStatus;
use Illuminate\Support\Facades\DB;

class OnboardingService implements OnboardingServiceInterface
{
    protected $userRepo;
    protected $workspaceRepo;

    public function __construct(
        UserRepositoryInterface $userRepo,
        WorkspaceRepositoryInterface $workspaceRepo
    ) {
        $this->userRepo = $userRepo;
        $this->workspaceRepo = $workspaceRepo;
    }

    public function completeOnboarding(User $user, array $data): Workspace
    {
        return DB::transaction(function () use ($user, $data) {
            // 1. Update Profile User
            $user->update([
                'full_name' => $data['full_name'],
            ]);

            // 2. Create Workspace
            $workspace = $this->workspaceRepo->create([
                'name' => $data['workspace_name'],
                'description' => $data['workspace_description'] ?? null,
                'owner_id' => $user->id,
            ]);

            // 3. Tambahkan ke Workspace Members sebagai Owner
            \App\Models\WorkspaceMember::create([
                'workspace_id' => $workspace->id,
                'user_id' => $user->id,
                'role_workspace' => 'owner',
                'status_invitation' => 'accepted',
                'joined_at' => now(),
            ]);

            // 4. Jika user memilih untuk membuat template project default
            if (!empty($data['create_default_project'])) {
                $project = Project::create([
                    'workspace_id' => $workspace->id,
                    'name' => $data['project_name'] ?? 'Proyek Utama',
                    'description' => $data['project_description'] ?? 'Proyek default untuk memulai kolaborasi.',
                    'created_by' => $user->id,
                ]);

                // Daftarkan sebagai Project Leader
                ProjectMember::create([
                    'project_id' => $project->id,
                    'user_id' => $user->id,
                    'role_project' => 'leader',
                    'joined_at' => now(),
                ]);

                // Buat default Kanban task statuses
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
            }

            return $workspace;
        });
    }
}
