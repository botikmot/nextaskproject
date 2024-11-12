<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProjectUserRole extends Model
{
    protected $table = 'project_user_role';
    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid(); // Generate UUID if it's not already set
            }
        });
    }
}
