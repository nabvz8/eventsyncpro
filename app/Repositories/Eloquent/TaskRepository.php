<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\TaskRepositoryInterface;
use App\Models\Task;
use App\Models\TaskStatus;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository implements TaskRepositoryInterface
{
    /**
     * Membuat task baru.
     */
    public function create(array $data): Task
    {
        return Task::create($data);
    }

    /**
     * Mencari task berdasarkan ID.
     */
    public function findById(string $id): ?Task
    {
        return Task::with(['assignee', 'creator', 'team', 'status'])->find($id);
    }

    /**
     * Mendapatkan data board Kanban: semua status dengan task-nya.
     * Jika $teamId diberikan, task akan difilter hanya untuk tim tersebut.
     */
    public function getBoardData(string $projectId, ?string $teamId = null): Collection
    {
        return TaskStatus::where('project_id', $projectId)
            ->with([
                'tasks' => function ($query) use ($teamId) {
                    if ($teamId) {
                        $query->where('team_id', $teamId);
                    }
                    $query->with(['assignee:id,full_name,username', 'team:id,name'])
                          ->orderBy('position');
                }
            ])
            ->orderBy('position')
            ->get();
    }

    /**
     * Memperbarui task berdasarkan ID.
     */
    public function update(string $id, array $data): bool
    {
        $task = Task::find($id);
        if (!$task) {
            return false;
        }
        return $task->update($data);
    }
}
