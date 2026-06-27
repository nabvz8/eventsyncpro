<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TeamMember extends Pivot
{
    use HasUuids;

    protected $table = 'team_members';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'team_id',
        'user_id',
        'role_team',
    ];

    /**
     * Relasi ke Tim.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Relasi ke User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
