<?php

namespace App\Repositories\Eloquent;

use App\Models\Workspace;
use App\Contracts\Repositories\WorkspaceRepositoryInterface;

class WorkspaceRepository implements WorkspaceRepositoryInterface
{
    public function create(array $data): Workspace
    {
        return Workspace::create($data);
    }

    public function findById(string $id): ?Workspace
    {
        return Workspace::with('owner')->find($id);
    }

    public function getUserWorkspaces(string $userId)
    {
        return Workspace::where('owner_id', $userId)
            ->orWhereHas('members', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->get();
    }
}
