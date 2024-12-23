<?php
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{id}', function (User $user, $id) {
    //return (int) $user->id === (int) $id;
    return $user->conversations->contains($id);
});
