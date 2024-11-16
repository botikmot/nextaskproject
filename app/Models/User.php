<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_image',
        'last_login',
        'google_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid(); // Automatically generate UUID on creation
            }
        });
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function projectMemberships()
    {
        return $this->belongsToMany(Project::class);
    }

    public function tasks()
    {
        //return $this->hasMany(Task::class);
        return $this->belongsToMany(Task::class, 'task_user');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'project_user_role')
                    ->withPivot('project_id')
                    ->withTimestamps();
    }

    public function projectsWithRoles()
    {
        return $this->belongsToMany(Project::class, 'project_user_role')
                    ->withPivot('role_id')
                    ->withTimestamps();
    }

    public function mainRoles()
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    // Method to check if a user has a specific role
    public function hasRole($roleName)
    {
        return $this->mainRoles()->where('name', $roleName)->exists();
    }

    // Method to assign a role to the user
    public function assignRole($role)
    {
        $this->mainRoles()->attach($role);
    }




}
