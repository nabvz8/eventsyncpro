<?php

namespace App\Services;

use App\Contracts\Services\AuthServiceInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService implements AuthServiceInterface
{
    protected $userRepo;

    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function register(array $data): User
    {
        $userData = [
            'email' => $data['email'],
            'password_hash' => Hash::make($data['password']),
            'username' => $data['username'],
            'full_name' => $data['full_name'],
            'role_global' => $data['role_global'] ?? 'user',
        ];

        $user = $this->userRepo->create($userData);

        Auth::login($user);

        return $user;
    }

    public function login(array $credentials): bool
    {
        $user = $this->userRepo->findByEmail($credentials['email']);

        if (!$user || !Hash::check($credentials['password'], $user->password_hash)) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        Auth::login($user, $credentials['remember'] ?? false);

        request()->session()->regenerate();

        return true;
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();

        request()->session()->invalidate();

        request()->session()->regenerateToken();
    }
}
