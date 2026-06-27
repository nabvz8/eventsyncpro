<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectMember extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'project_members';

    protected $fillable = [
        'project_id',
        'user_id',
        'role_project',
        'joined_at',
    ];

    /**
     * Relasi ke Project.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Relasi ke User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
