<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'deadline', 'status', 'user_id'];

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
}
