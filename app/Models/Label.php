<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Label extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = ['name', 'color', 'project_id'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid(); // Automatically generate UUID on creation
            }
        });
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_label');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
