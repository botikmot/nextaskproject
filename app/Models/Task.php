<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Task extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = ['title', 'description', 'user_id', 'project_id', 'status_id', 'priority', 'index', 'due_date', 'status', 'labels'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid(); // Automatically generate UUID on creation
            }
        });
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function users() {
        return $this->belongsToMany(User::class, 'task_user');
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function comments() {
        return $this->hasMany(TaskComment::class);
    }

    public function subtasks()
    {
        return $this->hasMany(Subtask::class, 'parent_id');
    }

    public function dependencies()
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'task_id', 'depends_on_task_id');
    }

    public function dependentTasks()
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'depends_on_task_id', 'task_id');
    }

    public function getProgressAttribute()
    {
        $totalSubtasks = $this->subtasks()->count();
        $completedSubtasks = $this->subtasks()->where('is_completed', true)->count();

        return $totalSubtasks > 0 ? round(($completedSubtasks / $totalSubtasks) * 100, 0) : 0;
    }

    public function histories()
    {
        return $this->hasMany(TaskHistory::class)->orderBy('created_at', 'desc');
    }



}
