<?php

namespace App\Http\Controllers;

use App\Contracts\Services\TaskServiceInterface;
use App\Contracts\Repositories\TaskRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\Access\AuthorizationException;

class TaskController extends Controller
{
    protected $taskService;
    protected $taskRepo;

    public function __construct(
        TaskServiceInterface $taskService,
        TaskRepositoryInterface $taskRepo
    ) {
        $this->taskService = $taskService;
        $this->taskRepo = $taskRepo;
    }

    /**
     * Membuat task baru dalam proyek.
     */
    public function store(Request $request, string $projId): RedirectResponse
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'team_id'     => 'nullable|uuid|exists:teams,id',
            'assigned_to' => 'nullable|uuid|exists:users,id',
            'priority'    => 'nullable|string|in:low,medium,high,urgent',
            'due_date'    => 'nullable|date',
            'status_id'   => 'nullable|uuid|exists:task_statuses,id',
        ]);

        $validated['project_id'] = $projId;

        try {
            $this->taskService->createTask($validated, $request->user());
        } catch (AuthorizationException $e) {
            abort(403, $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['task' => $e->getMessage()]);
        }

        return back();
    }

    /**
     * Memperbarui task (pindah kolom Kanban / edit detail).
     * Mengembalikan JSON untuk mendukung update AJAX dari drag-and-drop.
     */
    public function update(Request $request, string $taskId): JsonResponse
    {
        $validated = $request->validate([
            'status_id' => 'nullable|uuid|exists:task_statuses,id',
            'position'  => 'nullable|integer|min:0',
            'title'     => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'priority'  => 'nullable|string|in:low,medium,high,urgent',
            'due_date'  => 'nullable|date',
            'assigned_to' => 'nullable|uuid|exists:users,id',
            'team_id'   => 'nullable|uuid|exists:teams,id',
        ]);

        try {
            $this->taskService->moveTask($taskId, $validated, $request->user());
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

        return response()->json(['message' => 'Task berhasil diperbarui.']);
    }
}
