<?php

namespace App\Contracts\Repositories;

interface TaskRepositoryInterface
{
    /**
     * Membuat task baru.
     */
    public function create(array $data): \App\Models\Task;

    /**
     * Mencari task berdasarkan ID.
     */
    public function findById(string $id): ?\App\Models\Task;

    /**
     * Mendapatkan semua task dalam satu proyek, dikelompokkan ke dalam status Kanban.
     * Mendukung filter berdasarkan team_id untuk anggota biasa.
     */
    public function getBoardData(string $projectId, ?string $teamId = null): \Illuminate\Database\Eloquent\Collection;

    /**
     * Memperbarui task (status, posisi, atau field lainnya).
     */
    public function update(string $id, array $data): bool;
}
