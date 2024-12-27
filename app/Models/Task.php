<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Carbon\Carbon;


class Task extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    protected $appends = ['progress'];

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
        return $this->belongsToMany(User::class, 'task_user')->withTimestamps();
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

    public function labels()
    {
        return $this->belongsToMany(Label::class, 'task_label');
    }

    public function scopeAssignedThisWeek($query, $userId)
    {
        $startOfWeek = Carbon::now()->startOfWeek(); // Start of the week
        $endOfWeek = Carbon::now()->endOfWeek();     // End of the week

        return $query->whereHas('users', function ($query) use ($userId, $startOfWeek, $endOfWeek) {
            $query->where('user_id', $userId)  // User is assigned
                ->whereBetween('task_user.created_at', [$startOfWeek, $endOfWeek]); // Filter by week range
        });
    }

    public function scopeCompletedThisWeek($query, $userId)
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        return $query->whereHas('users', function ($query) use ($userId, $startOfWeek, $endOfWeek) {
            $query->where('user_id', $userId)
                ->whereBetween('task_user.created_at', [$startOfWeek, $endOfWeek]);
        })
        ->whereHas('project', function ($query) {
            $query->whereColumn('completed_status_id', 'tasks.status_id');
        });
    }

    public function scopeDueToday($query)
    {
        $today = Carbon::today(); // Get today's date
        $userId = auth()->id(); // Get the authenticated user's ID

        return $query->whereDate('due_date', $today) // Filter by today's date
                 ->whereHas('project', function ($query) {
                     $query->whereColumn('completed_status_id', '!=', 'tasks.status_id'); // Use whereColumn for column-to-column comparison
                 })
                 ->whereHas('users', function ($query) use ($userId) {
                     $query->where('users.id', $userId); // Filter by authenticated user
                 });
    }

}
