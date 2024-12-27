<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Carbon\Carbon;
use App\Events\UserStatusChanged;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Update last activity timestamp for authenticated users
        if ($request->user()) {
            $request->user()->update(['last_login' => now()]);
            broadcast(new UserStatusChanged($request->user(), 'online'));
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'suggestedFriends' => fn () => $request->user()
                ? $request->user()->suggestedFriends()
                : [],
            'receivedFriendRequests' => fn () => $request->user()
                ? $request->user()->receivedFriendRequests->map(function ($friendRequest) use ($request) {
                    // Get the sender (the user who sent the friend request)
                    $sender = $friendRequest->sender;

                    // Calculate mutual projects between the current user and the sender
                    $mutualProjectsCount = $sender->projectMemberships->pluck('id')
                        ->intersect($request->user()->projectMemberships->pluck('id'))
                        ->count();

                    // Add mutual projects count to the sender
                    $sender->mutual_projects = $mutualProjectsCount;

                    // Return the updated friend request with the sender's mutual projects
                    return $friendRequest;
                })
                : [],
            'sharedFriends' => fn () => $request->user()
                ? $request->user()->friends->map(function ($friend) use ($request) {
                    $lastActivity = $friend->last_login ? Carbon::parse($friend->last_login) : null;
                    $isOnline = $lastActivity && $lastActivity->diffInMinutes(now()) < 5;
                    $friend->status = $isOnline ? 'online' : 'offline';
                    // Calculate mutual projects
                    $mutualProjectsCount = $friend->projectMemberships->pluck('id')
                        ->intersect($request->user()->projectMemberships->pluck('id'))
                        ->count();

                    // Add mutual projects count as a new attribute
                    $friend->mutual_projects = $mutualProjectsCount;

                    return $friend;
                })
                : [],
            'sharedConversations' => fn () => $request->user()
                ? [
                    'private' => $request->user()->privateConversations()
                        ->with([
                            'users:id,name,profile_image',
                            'messages' => fn ($query) => $query->latest()->limit(1),
                        ])
                        ->get()
                        ->map(function ($conversation) use ($request) {
                            // Add chat partner's name for private conversations
                            $chatPartner = $conversation->chatPartner($request->user()->id);
                            $conversation->chat_name = $chatPartner ? $chatPartner->name : 'Unknown User';
                            $conversation->chat_image = $chatPartner ? $chatPartner->profile_image : null;
                            $lastActivity = $chatPartner->last_login ? Carbon::parse($chatPartner->last_login) : null;
                            $isOnline = $lastActivity && $lastActivity->diffInMinutes(now()) < 5;
                            $conversation->status = $isOnline ? 'online' : 'offline';
                            $conversation->user_id = $chatPartner ? $chatPartner->id : null;

                            return $conversation;
                        }),
                    'group' => $request->user()->groupConversations()
                        ->with([
                            'users:id,name,profile_image',
                            'messages' => fn ($query) => $query->with('user')->latest()->limit(1),
                        ])
                        ->get()
                        ->unique('id'),
                ]
                : [],
            'notifications' => fn () => $request->user()
                ? $request->user()->unreadNotifications()->with('user')->get()
                : [],
        ];
    }
}
