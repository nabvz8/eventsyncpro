<?php

namespace App\Contracts\Services;

interface TaskServiceInterface
{
    /**
     * Membuat task baru dalam sebuah proyek.
     */
    public function createTask(array $data, \App\Models\User $currentUser): \App\Models\Task;

    /**
     * Memperbarui status dan/atau posisi task (untuk drag-and-drop Kanban).
     */
    public function moveTask(string $taskId, array $data, \App\Models\User $currentUser): bool;

    /**
     * Mendapatkan data papan Kanban berdasarkan otorisasi pengguna.
     * Project Leader/Co-Leader melihat semua task; anggota tim hanya melihat task timnya.
     */
    public function getBoardData(string $projectId, \App\Models\User $currentUser): \Illuminate\Database\Eloquent\Collection;
}
