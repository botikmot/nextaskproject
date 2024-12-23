<?php
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{id}', function (User $user, $id) {
    return $user->conversations->contains($id);
});

Broadcast::channel('users-status', function (User $user) {
    return true;  // Allow all authenticated users to listen to this channel
});