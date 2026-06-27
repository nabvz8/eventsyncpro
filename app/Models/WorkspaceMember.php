<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkspaceMember extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'workspace_members';

    protected $fillable = [
        'workspace_id',
        'user_id',
        'role_workspace',
        'status_invitation',
        'joined_at',
    ];

    /**
     * Relasi ke User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Workspace.
     */
    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
}
