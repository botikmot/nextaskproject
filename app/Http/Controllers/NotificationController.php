<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markNotificationChatAsRead(Request $request)
    {
        //dd($request->input('conversation_id'));
        $updated = auth()->user()->notifications()
        ->whereNull('read_at') // Unread notifications
        ->where('data->type', $request->input('type')) // Check if type is 'chat' in JSON data
        ->where('data->conversation_id', $request->input('conversation_id'))
        ->update(['read_at' => now()]); // Mark as read

        if ($updated > 0) {
            return response()->json(['success' => true, 'message' => "$updated chat notifications marked as read."]);
        }

        return response()->json(['success' => false, 'message' => 'No unread chat notifications found.']);
    }
}
