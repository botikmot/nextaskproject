<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;
use App\Events\MessageSent;
use App\Notifications\UserNotification;
use App\Notifications\ChatMessageNotification;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    public function createPrivateConversation(Request $request)
    {
        $request->validate([
            'friend_id' => 'required|exists:users,id',
        ]);

        $user = auth()->user();
        $friendId = $request->friend_id;

        // Check if a private conversation already exists between these two users
        $existingConversation = $user->privateConversationsWith($friendId)->first();
        if ($existingConversation) {
            return response()->json(['message' => 'Private conversation already exists.'], 400);
        }

        // Create a new conversation
        $conversation = Conversation::create([
            'type' => 'private',
        ]);

        // Add both users to the conversation
        $conversation->users()->attach($user->id, ['is_admin' => false]);
        $conversation->users()->attach($friendId, ['is_admin' => false]);

        $chatPartner = $conversation->chatPartner($user->id);
        $conversation->chat_name = $chatPartner ? $chatPartner->name : 'Unknown User';
        $conversation->chat_image = $chatPartner ? $chatPartner->profile_image : null;
        $conversation->messages = [];

        //return response()->json($conversation, 201);
        return response()->json([
            'success' => true,
            'conversation' => $conversation,
        ]);
    }

    public function createGroupConversation(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'participants' => 'required|array|min:1', // Participants' IDs
        ]);

        $user = auth()->user();
        $participants = $request->participants;

        // Create a new group conversation
        $conversation = Conversation::create([
            'name' => $request->name,
            'type' => 'group',
        ]);

        // Add the user and participants to the conversation
        $conversation->users()->attach($user->id, ['is_admin' => true]);
        foreach ($participants as $participantId) {
            $conversation->users()->attach($participantId, ['is_admin' => false]);
        }
        $conversation->messages = [];
        //return response()->json($conversation, 201);
        return response()->json([
            'success' => true,
            'conversation' => $conversation->load('users'),
        ]);
    }


    public function inviteToGroup(Request $request, $conversationId)
    {
        $request->validate([
            'participants' => 'required|array|min:1', // New participant IDs
        ]);

        $user = auth()->user();
        $conversation = Conversation::findOrFail($conversationId);

        // Ensure the user is an admin of the group
        if (!$conversation->admins->contains($user)) {
            return response()->json(['message' => 'You must be an admin to invite users.'], 403);
        }

        // Add participants to the group chat
        foreach ($request->participants as $participantId) {
            // Ensure the participant is not already in the conversation
            if (!$conversation->users->contains('id', $participantId)) {
                $conversation->users()->attach($participantId, ['is_admin' => false]);
            }
        }

        return response()->json($conversation, 200);
    }


    public function sendMessage(Request $request, $conversationId)
    {
        $request->validate([
            'text' => 'required|string',
        ]);

        $user = auth()->user();
        $conversation = Conversation::findOrFail($conversationId);
        
        // Ensure the user is part of the conversation
        if (!$conversation->hasUser($user->id)) {
            return response()->json(['message' => 'You are not part of this conversation.'], 403);
        }

        // Create the message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'text' => $request->text,
        ]);

        $otherParticipants = $conversation->users()->where('users.id', '!=', $user->id)->get();
        foreach ($otherParticipants as $participant) {

            //$participant->notify(new ChatMessageNotification($request->text, $user->name));
            // Using Laravel's built-in notification system
            $participant->notify(new UserNotification([
                'type' => 'chat',
                'message' => "{$user->name} sent you a message: {$message->text}",
                'user_id' => $user->id,
                'text' => $request->text,
                'conversation_id' => $conversationId,
            ]));
        }
        
        // Fire the MessageSent event to broadcast
        broadcast(new MessageSent($message->load('user')));
       
        return redirect()->back()->with([
            'success' => true,
            'message' => 'Send message successfully',
        ]);
    }


    public function getMessages($conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);

        // Retrieve all messages for the conversation
        $messages = $conversation->messages()->with('user')->get();

        return response()->json($messages);
    }

}
