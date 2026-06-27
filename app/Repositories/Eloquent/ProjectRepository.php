<?php

namespace App\Repositories\Eloquent;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Contracts\Repositories\ProjectRepositoryInterface;

class ProjectRepository implements ProjectRepositoryInterface
{
    public function create(array $data): Project
    {
        return Project::create($data);
    }

    public function findById(string $id): ?Project
    {
        return Project::with(['workspace', 'members', 'statuses', 'teams.members'])->find($id);
    }

    public function getWorkspaceProjects(string $workspaceId)
    {
        return Project::where('workspace_id', $workspaceId)->get();
    }

    public function addMember(string $projectId, string $userId, string $role): bool
    {
        $member = ProjectMember::updateOrCreate(
            ['project_id' => $projectId, 'user_id' => $userId],
            ['role_project' => $role]
        );

        return $member ? true : false;
    }

    public function removeMember(string $projectId, string $userId): bool
    {
        $deleted = ProjectMember::where('project_id', $projectId)
            ->where('user_id', $userId)
            ->delete();

        return $deleted > 0;
    }

    public function isMember(string $projectId, string $userId): bool
    {
        return ProjectMember::where('project_id', $projectId)
            ->where('user_id', $userId)
            ->exists();
    }
}
