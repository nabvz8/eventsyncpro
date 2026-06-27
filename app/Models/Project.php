<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'workspace_id',
        'name',
        'description',
        'created_by',
    ];

    /**
     * Relasi ke Workspace.
     */
    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Relasi ke pembuat proyek (User).
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke anggota proyek (User).
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'project_members')
                    ->withPivot('role_project')
                    ->withTimestamps();
    }

    /**
     * Relasi ke daftar status Kanban (TaskStatus).
     */
    public function statuses()
    {
        return $this->hasMany(TaskStatus::class)->orderBy('position');
    }

    /**
     * Relasi ke tim di bawah proyek (Team).
     */
    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    /**
     * Relasi ke semua tugas proyek (Task).
     */
    public function tasks()
    {
        return $this->hasMany(Task::class)->orderBy('position');
    }
}
