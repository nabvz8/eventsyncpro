<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\WorkspaceRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class WorkspaceController extends Controller
{
    protected $workspaceRepo;

    public function __construct(WorkspaceRepositoryInterface $workspaceRepo)
    {
        $this->workspaceRepo = $workspaceRepo;
    }

    /**
     * Membuat workspace baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $user = $request->user();

        DB::transaction(function () use ($user, $validated) {
            $workspace = $this->workspaceRepo->create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'owner_id' => $user->id,
            ]);

            $workspace->members()->attach($user->id, [
                'role_workspace' => 'owner',
                'status_invitation' => 'accepted',
                'joined_at' => now(),
            ]);
        });

        return back();
    }

    /**
     * Berpindah workspace aktif.
     */
    public function switch(Request $request, string $id): RedirectResponse
    {
        $user = $request->user();
        
        $hasAccess = \App\Models\Workspace::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('owner_id', $user->id)
                    ->orWhereHas('members', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })->exists();

        if ($hasAccess) {
            session(['active_workspace_id' => $id]);
        }

        return back();
    }
}
