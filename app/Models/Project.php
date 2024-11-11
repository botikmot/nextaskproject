<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'deadline', 'status', 'user_id', 'completed_status_id'];

    public function admin()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function members()
    {
        //return $this->belongsToMany(User::class);
        return $this->hasMany(ProjectUser::class);
    }

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
        $completedStatusId = $this->completed_status_id ?? $this->statuses()->orderByDesc('id')->first()->id;

        if (!$completedStatusId) {
            return 0;
        }

        $totalTasks = $this->statuses()->withCount('tasks')->get()->sum('tasks_count');
        $completedTasks = $this->statuses()->where('id', $completedStatusId)->first()->tasks()->count();

        return $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0;
    }


}
