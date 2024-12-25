<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Notification extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',                // Add the id to the fillable property
        'type',
        'notifiable_id',
        'notifiable_type',
        'data',
        'read_at',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function setDataAttribute($value)
    {
        $this->attributes['data'] = json_encode($value); // Serialize array/object to JSON
    }

    // Automatically deserialize the 'data' field when you access it
    public function getDataAttribute($value)
    {
        return json_decode($value, true); // Decode the JSON back into an array
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function notifiable()
    {
        return $this->morphTo();
    }
    
}
