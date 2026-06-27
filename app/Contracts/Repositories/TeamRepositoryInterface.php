<?php

namespace App\Contracts\Repositories;

use App\Models\Team;

interface TeamRepositoryInterface
{
    public function create(array $data): Team;
    public function findById(string $id): ?Team;
    public function addMember(string $teamId, string $userId, string $role): bool;
    public function removeMember(string $teamId, string $userId): bool;
    public function isMember(string $teamId, string $userId): bool;
}
