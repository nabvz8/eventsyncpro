<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Contracts\Repositories\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function findById(string $id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(string $id, array $data): bool
    {
        $user = User::find($id);
        return $user ? $user->update($data) : false;
    }
}
