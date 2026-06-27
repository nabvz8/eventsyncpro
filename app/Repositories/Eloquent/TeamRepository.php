<?php

namespace App\Repositories\Eloquent;

use App\Models\Team;
use App\Models\TeamMember;
use App\Contracts\Repositories\TeamRepositoryInterface;

class TeamRepository implements TeamRepositoryInterface
{
    public function create(array $data): Team
    {
        return Team::create($data);
    }

    public function findById(string $id): ?Team
    {
        return Team::with(['project', 'members'])->find($id);
    }

    public function addMember(string $teamId, string $userId, string $role): bool
    {
        $member = TeamMember::updateOrCreate(
            ['team_id' => $teamId, 'user_id' => $userId],
            ['role_team' => $role]
        );

        return $member ? true : false;
    }

    public function removeMember(string $teamId, string $userId): bool
    {
        $deleted = TeamMember::where('team_id', $teamId)
            ->where('user_id', $userId)
            ->delete();

        return $deleted > 0;
    }

    public function isMember(string $teamId, string $userId): bool
    {
        return TeamMember::where('team_id', $teamId)
            ->where('user_id', $userId)
            ->exists();
    }
}
