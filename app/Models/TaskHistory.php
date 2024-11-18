<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskHistory extends Model
{
    protected $fillable = ['task_id', 'user_id', 'attribute', 'old_value', 'new_value'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function oldStatus()
    {
        return $this->belongsTo(Status::class, 'old_value', 'id');
    }

    public function newStatus()
    {
        return $this->belongsTo(Status::class, 'new_value', 'id');
    }
}
