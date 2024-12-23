<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Attachment extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = ['path', 'filename'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid(); // Automatically generate UUID on creation
            }
        });
    }

    public function attachable()
    {
        return $this->morphTo('attachable');
    }
}
