<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Carbon\Carbon;
use App\Events\UserStatusChanged;
use App\Models\Challenge;

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
            'getAllEvents' => fn () => $request->user()
                ? $request->user()->getAllEvents()
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
            'participantChallenges' => fn () => $request->user()
                ? Challenge::with('rewards', 'users') // Include relationships
                    ->where(function ($query) use ($request) {
                        $query->whereHas('users', function ($query) use ($request) {
                            // Filter challenges where the authenticated user is a participant
                            $query->where('user_id', $request->user()->id);
                        })
                        ->orWhere('user_id', $request->user()->id); // Include challenges where the auth user is the creator
                    })
                    ->get()
                    ->map(function ($challenge) use ($request) {
                        $participantPoints = $challenge->getParticipantPoints();
    
                        // Attach participant points and percentage to each user in the users array
                        $challenge->users = $challenge->users->map(function ($user) use ($participantPoints, $challenge) {
                            $totalPoints = $participantPoints[$user->id]['total_points'] ?? 0; // Get total points for the user
                            $challengePoints = $challenge->points; // Total points of the challenge
                            // Calculate percentage
                            $percentage = $challengePoints > 0 ? ($totalPoints / $challengePoints) * 100 : 0;
    
                            // Add participant points and percentage to the user object
                            $user->participant_points = $totalPoints;
                            $user->completion_percentage = round($percentage, 2); // Round to 2 decimal places
                            return $user;
                        });
    
                        return $challenge;
                })
                : [],
            'userLevel' => fn () => $request->user()
                ? $request->user()->level()->first()
                : [],
            'badges' => fn () => $request->user()
                ? $request->user()->badges()->get()
                : [],
            'tasksAheadOfDeadline' => fn () => $request->user()
                ? $request->user()->tasksAheadOfDeadline()
                : [],
        ];
    }
}
