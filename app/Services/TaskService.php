<?php

namespace App\Services;

use App\Contracts\Services\TaskServiceInterface;
use App\Contracts\Repositories\TaskRepositoryInterface;
use App\Contracts\Repositories\ProjectRepositoryInterface;
use App\Models\Task;
use App\Models\ProjectMember;
use App\Models\TeamMember;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;

class TaskService implements TaskServiceInterface
{
    protected $taskRepo;
    protected $projectRepo;

    public function __construct(
        TaskRepositoryInterface $taskRepo,
        ProjectRepositoryInterface $projectRepo
    ) {
        $this->taskRepo = $taskRepo;
        $this->projectRepo = $projectRepo;
    }

    /**
     * Menentukan apakah user adalah project leader/co-leader atau workspace owner.
     */
    private function isProjectLeaderOrOwner(string $projectId, User $user): bool
    {
        $project = $this->projectRepo->findById($projectId);
        if (!$project) return false;

        $isWorkspaceOwner = $project->workspace->owner_id === $user->id;
        if ($isWorkspaceOwner) return true;

        $projectMember = ProjectMember::where('project_id', $projectId)
            ->where('user_id', $user->id)
            ->first();

        return $projectMember && in_array($projectMember->role_project, ['leader', 'co_leader']);
    }

    /**
     * Mendapatkan team_id dari user dalam proyek (null jika tidak ada atau adalah leader).
     */
    private function getUserTeamId(string $projectId, User $user): ?string
    {
        $teamMember = TeamMember::whereHas('team', function ($q) use ($projectId) {
            $q->where('project_id', $projectId);
        })->where('user_id', $user->id)->first();

        return $teamMember ? $teamMember->team_id : null;
    }

    /**
     * Mendapatkan data papan Kanban berdasarkan otorisasi pengguna.
     */
    public function getBoardData(string $projectId, User $currentUser): Collection
    {
        // Jika project leader/co-leader/workspace owner → tampilkan semua task
        if ($this->isProjectLeaderOrOwner($projectId, $currentUser)) {
            return $this->taskRepo->getBoardData($projectId, null);
        }

        // Jika anggota tim biasa → tampilkan hanya task tim-nya
        $teamId = $this->getUserTeamId($projectId, $currentUser);
        return $this->taskRepo->getBoardData($projectId, $teamId);
    }

    /**
     * Membuat task baru dalam proyek.
     */
    public function createTask(array $data, User $currentUser): Task
    {
        $projectId = $data['project_id'];
        $project = $this->projectRepo->findById($projectId);
        if (!$project) {
            throw new \InvalidArgumentException("Proyek tidak ditemukan.");
        }

        // Validasi akses: harus anggota workspace
        $workspace = $project->workspace;
        $isWorkspaceMember = $workspace->owner_id === $currentUser->id
            || $workspace->members()->where('user_id', $currentUser->id)->exists();
        if (!$isWorkspaceMember) {
            throw new AuthorizationException("Anda tidak memiliki akses ke proyek ini.");
        }

        // Jika task diassign ke tim, pastikan user adalah anggota tim tersebut atau leader proyek
        if (!empty($data['team_id'])) {
            $isLeader = $this->isProjectLeaderOrOwner($projectId, $currentUser);
            $isTeamMember = TeamMember::where('team_id', $data['team_id'])
                ->where('user_id', $currentUser->id)
                ->exists();
            if (!$isLeader && !$isTeamMember) {
                throw new AuthorizationException("Anda hanya dapat membuat task di tim Anda sendiri.");
            }
        }

        // Dapatkan status default (To Do) jika tidak diberikan
        if (empty($data['status_id'])) {
            $defaultStatus = TaskStatus::where('project_id', $projectId)
                ->orderBy('position')
                ->first();
            if (!$defaultStatus) {
                throw new \InvalidArgumentException("Status Kanban belum tersedia untuk proyek ini.");
            }
            $data['status_id'] = $defaultStatus->id;
        }

        // Hitung posisi task terbaru dalam kolom tersebut
        $maxPosition = Task::where('status_id', $data['status_id'])->max('position') ?? 0;
        $data['position'] = $maxPosition + 1;
        $data['created_by'] = $currentUser->id;

        return $this->taskRepo->create($data);
    }

    /**
     * Memindahkan task ke status atau posisi baru (drag-and-drop).
     */
    public function moveTask(string $taskId, array $data, User $currentUser): bool
    {
        $task = $this->taskRepo->findById($taskId);
        if (!$task) {
            throw new \InvalidArgumentException("Task tidak ditemukan.");
        }

        // Validasi otorisasi: harus anggota workspace proyek
        $project = $task->project;
        $workspace = $project->workspace;
        $isWorkspaceMember = $workspace->owner_id === $currentUser->id
            || $workspace->members()->where('user_id', $currentUser->id)->exists();
        if (!$isWorkspaceMember) {
            throw new AuthorizationException("Anda tidak memiliki akses ke task ini.");
        }

        // Jika bukan leader, pastikan anggota tim hanya bisa memindahkan task timnya
        if (!$this->isProjectLeaderOrOwner($task->project_id, $currentUser)) {
            $userTeamId = $this->getUserTeamId($task->project_id, $currentUser);
            if ($task->team_id && $task->team_id !== $userTeamId) {
                throw new AuthorizationException("Anda tidak bisa memindahkan task dari tim lain.");
            }
        }

        return $this->taskRepo->update($taskId, $data);
    }
}
