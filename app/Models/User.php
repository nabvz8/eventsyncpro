<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Database\Factories\UserFactory;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'email_verified_at',
        'password_hash',
        'password', // Alias untuk compatibility
        'username',
        'full_name',
        'avatar_url',
        'role_global',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    /**
     * Cast attributes.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    /**
     * Mutator & Accessor untuk attribute 'password' (compatibility Laravel Breeze/Auth).
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password_hash'] = $value;
    }

    public function getPasswordAttribute()
    {
        return $this->password_hash;
    }

    /**
     * Override field password default Laravel.
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Relasi ke Workspace yang dimiliki (Owner).
     */
    public function workspaces()
    {
        return $this->hasMany(Workspace::class, 'owner_id');
    }

    /**
     * Relasi ke Workspace tempat user bergabung sebagai anggota.
     */
    public function joinedWorkspaces()
    {
        return $this->belongsToMany(Workspace::class, 'workspace_members')
                    ->withPivot('role_workspace', 'status_invitation')
                    ->withTimestamps();
    }
}
