<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'project_id',
        'team_id',
        'status_id',
        'assigned_to',
        'created_by',
        'title',
        'description',
        'priority',
        'due_date',
        'position',
    ];

    protected $casts = [
        'due_date' => 'date',
        'position' => 'integer',
    ];

    /**
     * Relasi ke Proyek.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Relasi ke Tim Pelaksana.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Relasi ke Status Kanban.
     */
    public function status()
    {
        return $this->belongsTo(TaskStatus::class, 'status_id');
    }

    /**
     * Relasi ke User yang ditugaskan.
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Relasi ke User yang membuat task.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
