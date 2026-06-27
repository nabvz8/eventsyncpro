<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\TaskStatus;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\Task;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Membuat Pengguna Owner
        $owner = User::create([
            'full_name' => 'EventSync Owner',
            'username' => 'eventsync_owner',
            'email' => 'owner@eventsync.com',
            'password_hash' => Hash::make('password'),
            'role_global' => 'user',
        ]);

        // 2. Membuat Pengguna Member
        $member = User::create([
            'full_name' => 'EventSync Member',
            'username' => 'eventsync_member',
            'email' => 'member@eventsync.com',
            'password_hash' => Hash::make('password'),
            'role_global' => 'user',
        ]);

        // 3. Membuat Workspace Utama
        $workspace = Workspace::create([
            'name' => 'EventSync Pro Workspace',
            'description' => 'Workspace utama untuk pengerjaan proyek EventSync Pro.',
            'owner_id' => $owner->id,
        ]);

        // 4. Mendaftarkan Anggota Workspace
        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
            'role_workspace' => 'owner',
            'status_invitation' => 'accepted',
            'joined_at' => now(),
        ]);

        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id' => $member->id,
            'role_workspace' => 'member',
            'status_invitation' => 'accepted',
            'joined_at' => now(),
        ]);

        // 5. Membuat Proyek Inti
        $project = Project::create([
            'workspace_id' => $workspace->id,
            'name' => 'EventSync Core System',
            'description' => 'Proyek pengembangan sistem inti EventSync Pro MVP.',
            'created_by' => $owner->id,
        ]);

        // 6. Mendaftarkan Kepemimpinan Proyek
        ProjectMember::create([
            'project_id' => $project->id,
            'user_id' => $owner->id,
            'role_project' => 'leader',
        ]);

        ProjectMember::create([
            'project_id' => $project->id,
            'user_id' => $member->id,
            'role_project' => 'co_leader',
        ]);

        // 7. Membuat Status Kolom Kanban Default
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

        // 8. Membuat Tim Pelaksana Proyek
        $frontendTeam = Team::create([
            'project_id' => $project->id,
            'name' => 'Frontend developers',
            'description' => 'Tim yang menangani antarmuka pengguna Vue 3.',
        ]);

        $backendTeam = Team::create([
            'project_id' => $project->id,
            'name' => 'Backend developers',
            'description' => 'Tim yang menangani Laravel 12 API, database, dan real-time Reverb.',
        ]);

        // 9. Mendaftarkan Anggota ke Tim
        TeamMember::create([
            'team_id' => $frontendTeam->id,
            'user_id' => $member->id,
            'role_team' => 'leader',
        ]);

        TeamMember::create([
            'team_id' => $backendTeam->id,
            'user_id' => $owner->id,
            'role_team' => 'leader',
        ]);

        TeamMember::create([
            'team_id' => $backendTeam->id,
            'user_id' => $member->id,
            'role_team' => 'member',
        ]);

        // 10. Membuat Contoh Tasks di Papan Kanban
        $statusTodo = TaskStatus::where('project_id', $project->id)->where('name', 'To Do')->first();
        $statusInProgress = TaskStatus::where('project_id', $project->id)->where('name', 'In Progress')->first();
        $statusDone = TaskStatus::where('project_id', $project->id)->where('name', 'Done')->first();

        if ($statusTodo && $statusInProgress && $statusDone) {
            Task::create([
                'project_id' => $project->id,
                'team_id' => $backendTeam->id,
                'status_id' => $statusTodo->id,
                'created_by' => $owner->id,
                'assigned_to' => $owner->id,
                'title' => 'Setup konfigurasi PostgreSQL dan migrasi database awal',
                'description' => 'Konfigurasi koneksi pgsql di .env dan membuat semua file migrasi entitas utama.',
                'priority' => 'high',
                'due_date' => now()->addDays(3)->toDateString(),
                'position' => 0,
            ]);

            Task::create([
                'project_id' => $project->id,
                'team_id' => $frontendTeam->id,
                'status_id' => $statusInProgress->id,
                'created_by' => $owner->id,
                'assigned_to' => $member->id,
                'title' => 'Desain komponen Kanban Board dengan Motion JS',
                'description' => 'Membuat KanbanBoard.vue, TaskCard.vue, dan mengimplementasikan drag-and-drop HTML5.',
                'priority' => 'urgent',
                'due_date' => now()->addDays(1)->toDateString(),
                'position' => 0,
            ]);

            Task::create([
                'project_id' => $project->id,
                'team_id' => $frontendTeam->id,
                'status_id' => $statusInProgress->id,
                'created_by' => $member->id,
                'assigned_to' => $member->id,
                'title' => 'Implementasi Onboarding Wizard (3 langkah)',
                'description' => 'Step 1: Profil, Step 2: Workspace, Step 3: Template project default.',
                'priority' => 'medium',
                'due_date' => now()->addDays(5)->toDateString(),
                'position' => 1,
            ]);

            Task::create([
                'project_id' => $project->id,
                'team_id' => $backendTeam->id,
                'status_id' => $statusDone->id,
                'created_by' => $owner->id,
                'assigned_to' => $owner->id,
                'title' => 'Implementasi Service-Repository Pattern (Auth)',
                'description' => 'Membuat AuthService, AuthRepository, dan mendaftarkan binding di AppServiceProvider.',
                'priority' => 'high',
                'due_date' => now()->subDays(2)->toDateString(),
                'position' => 0,
            ]);

            Task::create([
                'project_id' => $project->id,
                'team_id' => null,
                'status_id' => $statusTodo->id,
                'created_by' => $owner->id,
                'assigned_to' => null,
                'title' => 'Menulis dokumentasi API endpoint dan rute sistem',
                'description' => 'Dokumentasi semua endpoint tersedia untuk tim FE agar integrasi lebih mudah.',
                'priority' => 'low',
                'due_date' => now()->addDays(14)->toDateString(),
                'position' => 1,
            ]);
        }
    }
}
