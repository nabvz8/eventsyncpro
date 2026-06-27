<?php

namespace App\Contracts\Repositories;

use App\Models\Workspace;

interface WorkspaceRepositoryInterface
{
    public function create(array $data): Workspace;
    public function findById(string $id): ?Workspace;
    public function getUserWorkspaces(string $userId);
}
