<?php

namespace App\Services;

use App\Contracts\Services\TeamServiceInterface;
use App\Contracts\Repositories\TeamRepositoryInterface;
use App\Contracts\Repositories\ProjectRepositoryInterface;
use App\Models\Team;
use App\Models\ProjectMember;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class TeamService implements TeamServiceInterface
{
    protected $teamRepo;
    protected $projectRepo;

    public function __construct(
        TeamRepositoryInterface $teamRepo,
        ProjectRepositoryInterface $projectRepo
    ) {
        $this->teamRepo = $teamRepo;
        $this->projectRepo = $projectRepo;
    }

    public function createTeam(array $data, User $currentUser): Team
    {
        $project = $this->projectRepo->findById($data['project_id']);
        if (!$project) {
            throw new \InvalidArgumentException("Proyek tidak ditemukan.");
        }

        $workspace = $project->workspace;

        // Cek otorisasi pembuatan tim
        $isWorkspaceOwner = $workspace->owner_id === $currentUser->id;
        $projectRole = ProjectMember::where('project_id', $project->id)
            ->where('user_id', $currentUser->id)
            ->first();

        $isProjectLeader = $projectRole && in_array($projectRole->role_project, ['leader', 'co_leader']);

        if (!$isWorkspaceOwner && !$isProjectLeader) {
            throw new AuthorizationException("Anda tidak memiliki hak akses untuk membuat tim di proyek ini.");
        }

        return $this->teamRepo->create([
            'project_id' => $data['project_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
    }

    public function addTeamMember(string $teamId, array $memberData, User $currentUser): bool
    {
        $team = $this->teamRepo->findById($teamId);
        if (!$team) {
            return false;
        }

        $project = $team->project;
        $workspace = $project->workspace;

        // Cek otorisasi penambahan anggota tim
        $isWorkspaceOwner = $workspace->owner_id === $currentUser->id;
        
        $projectRole = ProjectMember::where('project_id', $project->id)
            ->where('user_id', $currentUser->id)
            ->first();
        $isProjectLeader = $projectRole && in_array($projectRole->role_project, ['leader', 'co_leader']);

        $teamRole = TeamMember::where('team_id', $teamId)
            ->where('user_id', $currentUser->id)
            ->first();
        $isTeamLeader = $teamRole && in_array($teamRole->role_team, ['leader', 'co_leader']);

        if (!$isWorkspaceOwner && !$isProjectLeader && !$isTeamLeader) {
            throw new AuthorizationException("Anda tidak memiliki hak akses untuk menambah anggota ke tim ini.");
        }

        // Pastikan user yang ditambahkan adalah anggota workspace
        $isWorkspaceMember = $workspace->members()->where('user_id', $memberData['user_id'])->exists() || $workspace->owner_id === $memberData['user_id'];
        if (!$isWorkspaceMember) {
            throw new \InvalidArgumentException("User yang dipilih harus merupakan anggota workspace ini.");
        }

        return $this->teamRepo->addMember($teamId, $memberData['user_id'], $memberData['role']);
    }
}
