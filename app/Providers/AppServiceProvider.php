<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Repositories\Eloquent\UserRepository;
use App\Contracts\Services\AuthServiceInterface;
use App\Services\AuthService;
use App\Contracts\Repositories\WorkspaceRepositoryInterface;
use App\Repositories\Eloquent\WorkspaceRepository;
use App\Contracts\Services\OnboardingServiceInterface;
use App\Services\OnboardingService;
use App\Contracts\Repositories\ProjectRepositoryInterface;
use App\Repositories\Eloquent\ProjectRepository;
use App\Contracts\Services\ProjectServiceInterface;
use App\Services\ProjectService;
use App\Contracts\Repositories\TeamRepositoryInterface;
use App\Repositories\Eloquent\TeamRepository;
use App\Contracts\Services\TeamServiceInterface;
use App\Services\TeamService;
use App\Contracts\Repositories\TaskRepositoryInterface;
use App\Repositories\Eloquent\TaskRepository;
use App\Contracts\Services\TaskServiceInterface;
use App\Services\TaskService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind Repositories
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(WorkspaceRepositoryInterface::class, WorkspaceRepository::class);
        $this->app->bind(ProjectRepositoryInterface::class, ProjectRepository::class);
        $this->app->bind(TeamRepositoryInterface::class, TeamRepository::class);
        $this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);

        // Bind Services
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(OnboardingServiceInterface::class, OnboardingService::class);
        $this->app->bind(ProjectServiceInterface::class, ProjectService::class);
        $this->app->bind(TeamServiceInterface::class, TeamService::class);
        $this->app->bind(TaskServiceInterface::class, TaskService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
