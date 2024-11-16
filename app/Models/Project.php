<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = ['title', 'description', 'deadline', 'status', 'user_id', 'completed_status_id'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid(); // Automatically generate UUID on creation
            }
        });
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /* public function members()
    {
        //return $this->belongsToMany(User::class);
        return $this->hasMany(ProjectUser::class);
    } */

    public function statuses()
    {
        return $this->hasMany(Status::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function getProgressAttribute()
    {
        $completedStatusId = $this->completed_status_id ?? $this->statuses()->orderByDesc('created_at')->first()?->id;

        if (!$completedStatusId) {
            return 0;
        }

        $totalTasks = $this->statuses()->withCount('tasks')->get()->sum('tasks_count');
        $completedTasks = $this->statuses()->where('id', $completedStatusId)->first()->tasks()->count();

        return $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'project_user_role')
                    ->withPivot('role_id')
                    ->withTimestamps();
    }


    public function userHasPermission(User $user, $permissionName)
    {
        if ($user->id === $this->user_id) {
            return true;
        }
        // Get the role_id associated with the user in the project through the pivot table
        $userPivot = $this->users()->where('user_id', $user->id)->first();

        // Check if the user exists in the project, and then access the role_id
        if ($userPivot) {
            $role = $userPivot->pivot->role_id;
            
            // Check if the role associated with the user in this project has the required permission
            return Role::find($role)
                    ->permissions()
                    ->where('name', $permissionName)
                    ->exists();
        }

        // If the user is not found in the project, return false or handle appropriately
        return false;
    }


}
