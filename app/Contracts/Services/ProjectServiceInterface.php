<?php

namespace App\Contracts\Services;

use App\Models\Project;
use App\Models\User;

interface ProjectServiceInterface
{
    public function createProject(array $data, User $user): Project;
    public function addProjectMember(string $projectId, array $memberData, User $currentUser): bool;
}
