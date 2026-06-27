<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workspace extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'description',
        'logo_url',
        'owner_id',
    ];

    /**
     * Relasi ke owner (User).
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Relasi ke anggota workspace (User).
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'workspace_members')
                    ->withPivot('role_workspace', 'status_invitation')
                    ->withTimestamps();
    }

    /**
     * Relasi ke Projects di dalam Workspace.
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
