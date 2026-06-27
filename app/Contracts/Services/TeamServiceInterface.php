<?php

namespace App\Contracts\Services;

use App\Models\Team;
use App\Models\User;

interface TeamServiceInterface
{
    public function createTeam(array $data, User $currentUser): Team;
    public function addTeamMember(string $teamId, array $memberData, User $currentUser): bool;
}
