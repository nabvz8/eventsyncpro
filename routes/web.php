<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TaskController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Halaman Onboarding (hanya untuk yang belum onboarded)
Route::middleware(['auth', 'onboarded'])->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding');
    Route::post('/onboarding', [OnboardingController::class, 'store']);
});

// Rute Internal (memerlukan auth dan sudah onboarded)
Route::middleware(['auth', 'onboarded'])->group(function () {
    Route::get('/dashboard', function () {
        $activeWorkspaceId = session('active_workspace_id');
        $projects = collect();
        if ($activeWorkspaceId) {
            $projects = \App\Models\Project::where('workspace_id', $activeWorkspaceId)->get();
        }
        return Inertia::render('Dashboard', [
            'projects' => $projects,
        ]);
    })->name('dashboard');

    Route::post('/workspaces', [WorkspaceController::class, 'store'])->name('workspaces.store');
    Route::post('/workspaces/{id}/switch', [WorkspaceController::class, 'switch'])->name('workspaces.switch');

    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{id}', [ProjectController::class, 'show'])->name('projects.show');
    Route::post('/projects/{id}/members', [ProjectController::class, 'addMember'])->name('projects.members.store');
    Route::post('/projects/{projId}/teams', [TeamController::class, 'store'])->name('projects.teams.store');
    Route::post('/teams/{teamId}/members', [TeamController::class, 'addMember'])->name('teams.members.store');

    Route::post('/projects/{projId}/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{taskId}', [TaskController::class, 'update'])->name('tasks.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
