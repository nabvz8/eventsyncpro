<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $workspaces = collect();
        $activeWorkspace = null;

        if ($user) {
            $workspaces = \App\Models\Workspace::where('owner_id', $user->id)
                ->orWhereHas('members', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->get();

            if ($workspaces->isNotEmpty()) {
                $activeWorkspaceId = session('active_workspace_id');
                $activeWorkspace = $workspaces->firstWhere('id', $activeWorkspaceId) ?: $workspaces->first();
                if (!$activeWorkspaceId && $activeWorkspace) {
                    session(['active_workspace_id' => $activeWorkspace->id]);
                }
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'workspaces' => $workspaces->values()->all(),
                'active_workspace' => $activeWorkspace,
            ],
        ];
    }
}
