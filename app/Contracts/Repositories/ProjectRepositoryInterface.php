<?php

namespace App\Contracts\Repositories;

use App\Models\Project;

interface ProjectRepositoryInterface
{
    public function create(array $data): Project;
    public function findById(string $id): ?Project;
    public function getWorkspaceProjects(string $workspaceId);
    public function addMember(string $projectId, string $userId, string $role): bool;
    public function removeMember(string $projectId, string $userId): bool;
    public function isMember(string $projectId, string $userId): bool;
}
