<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'user_id', 'project_id', 'status_id', 'priority', 'index', 'due_date', 'status', 'labels'];

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
}
