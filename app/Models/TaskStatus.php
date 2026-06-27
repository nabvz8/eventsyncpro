<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskStatus extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'task_statuses';

    protected $fillable = [
        'project_id',
        'name',
        'color_hex',
        'position',
    ];

    /**
     * Relasi ke Project.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Relasi ke Tasks dalam status ini (urut berdasarkan posisi).
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'status_id')->orderBy('position');
    }
}
