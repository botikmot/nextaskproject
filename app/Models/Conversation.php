<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Conversation extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = ['name', 'type'];

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
        return $this->belongsToMany(User::class)->withPivot('is_admin');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    // Check if a user is part of the conversation
    public function hasUser($userId)
    {
        return $this->users->contains('id', $userId);
        //return $this->users()->where('id', $userId)->exists();
    }

    // Get admins of a group
    public function admins()
    {
        return $this->users()->wherePivot('is_admin', true);
    }

    public function chatPartner($authUserId)
    {
        // For private conversations, get the other user in the conversation
        return $this->users()
            ->select('users.id', 'users.name', 'users.profile_image') // Explicitly select necessary fields
            ->where('users.id', '!=', $authUserId)
            ->first();
    }


}
