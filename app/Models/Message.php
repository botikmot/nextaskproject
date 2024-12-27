<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'conversation_id', 'text'];

    // A message belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // A message belongs to a conversation
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function readers()
    {
        return $this->belongsToMany(User::class, 'message_reads')
                    ->withPivot('conversation_id')
                    ->withTimestamps();
    }

}
