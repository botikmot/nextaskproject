<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    protected $fillable = ['name', 'description', 'challenge_id', 'points_required'];

    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }
}
