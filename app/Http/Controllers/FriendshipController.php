<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FriendRequest;
use App\Models\Friendship;

class FriendshipController extends Controller
{
    
    public function send(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
        ]);

        // Check if a friend request already exists (either sent or received)
        $existingRequest = FriendRequest::where(function ($query) use ($request) {
            $query->where('sender_id', auth()->id())
                ->where('receiver_id', $request->receiver_id);
        })
        ->orWhere(function ($query) use ($request) {
            $query->where('sender_id', $request->receiver_id)
                ->where('receiver_id', auth()->id());
        })
        ->whereIn('status', ['pending', 'accepted'])
        ->exists();

        // If a request already exists, return with a message
        if ($existingRequest) {
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Friend request already sent or received.',
            ]);
        }

        $friendRequest = FriendRequest::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id,
        ]);

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Friend request sent successfully.', 
        ]);
    }

    public function accept($id)
    {
        $friendRequest = FriendRequest::with(['sender', 'receiver'])->findOrFail($id);

        // Update friend request status to 'accepted'
        $friendRequest->update(['status' => 'accepted']);

        // Attach friendships using the friends relationship
        $friendRequest->sender->friends()->attach($friendRequest->receiver_id);
        $friendRequest->receiver->friends()->attach($friendRequest->sender_id);

        // Get the updated list of friends and their mutual projects
        $friendsWithMutualProjects = $friendRequest->receiver->friends->map(function ($friend) use ($friendRequest) {
            // Calculate mutual projects between the current user and the friend
            $mutualProjectsCount = $friend->projectMemberships->pluck('id')
                ->intersect($friendRequest->receiver->projectMemberships->pluck('id'))
                ->count();

            $friend->mutual_projects = $mutualProjectsCount;
            return $friend;
        });


        return redirect()->back()->with([
            'success' => true,
            'message' => 'You have a new friend.',
            'friends' => $friendsWithMutualProjects,
        ]);
    }

    public function reject($id)
    {
        $friendRequest = FriendRequest::findOrFail($id);

        // Update friend request status to 'rejected'
        $friendRequest->update(['status' => 'rejected']);

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Successfully rejected.',
        ]);
    }
    
}
