<?php

namespace App\Contracts\Services;

use App\Models\User;
use App\Models\Workspace;

interface OnboardingServiceInterface
{
    public function completeOnboarding(User $user, array $data): Workspace;
}
