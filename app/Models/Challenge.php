<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Challenge extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = ['name', 'description', 'start_date', 'end_date', 'is_team_based', 'points', 'user_id'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('progress', 'completed')->withTimestamps();
    }

    public function rewards()
    {
        return $this->hasMany(Reward::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
