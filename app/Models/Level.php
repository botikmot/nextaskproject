<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Level extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'points_required', 'icon_svg'];

    // Relationship with users
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
