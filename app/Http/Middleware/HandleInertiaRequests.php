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
         $user = $request->user();
     
         // Update last activity and broadcast user status if authenticated
         if ($user) {
             $user->update(['last_login' => now()]);
             broadcast(new UserStatusChanged($user, 'online'));
         }
     
         // Preload relationships for optimization
         $user?->load([
             'friends.projectMemberships',
             'receivedFriendRequests.sender.projectMemberships',
             'privateConversations.messages',
             'privateConversations.users',
             'groupConversations.messages.user',
             'groupConversations.users',
             'badges',
         ]);
     
         // Helper function for mutual projects calculation
         $calculateMutualProjects = fn ($relatedUser) => $relatedUser->projectMemberships->pluck('id')
             ->intersect($user->projectMemberships->pluck('id'))
             ->count();
     
         return [
             ...parent::share($request),
             'auth' => [
                 'user' => $user,
             ],
             'suggestedFriends' => fn () => $user?->suggestedFriends() ?? [],
             'getAllEvents' => fn () => $user?->getAllEvents() ?? [],
             'receivedFriendRequests' => fn () => $user
                 ? $user->receivedFriendRequests->map(function ($friendRequest) use ($calculateMutualProjects) {
                     $sender = $friendRequest->sender;
                     $sender->mutual_projects = $calculateMutualProjects($sender);
                     return $friendRequest;
                 })
                 : [],
             'sharedFriends' => fn () => $user
                 ? $user->friends->map(function ($friend) use ($calculateMutualProjects) {
                     $lastActivity = $friend->last_login ? Carbon::parse($friend->last_login) : null;
                     $friend->status = $lastActivity && $lastActivity->diffInMinutes(now()) < 5 ? 'online' : 'offline';
                     $friend->mutual_projects = $calculateMutualProjects($friend);
                     $friend->badges = $friend->badges;
                     return $friend;
                 })
                 : [],
             'sharedConversations' => fn () => $user
                 ? [
                     'private' => $user->privateConversations->map(function ($conversation) use ($user) {
                         $chatPartner = $conversation->chatPartner($user->id);
                         $conversation->chat_name = $chatPartner->name ?? 'Unknown User';
                         $conversation->chat_image = $chatPartner->profile_image ?? null;
                         $conversation->status = $chatPartner && Carbon::parse($chatPartner->last_login)->diffInMinutes(now()) < 5
                             ? 'online'
                             : 'offline';
                         $conversation->user_id = $chatPartner->id ?? null;
                         return $conversation;
                     }),
                     'group' => $user->groupConversations->unique('id'),
                 ]
                 : [],
             'notifications' => fn () => $user?->unreadNotifications()->with('user')->get() ?? [],
             'participantChallenges' => fn () => $user
                 ? Challenge::with(['rewards', 'users'])
                     ->where(function ($query) use ($user) {
                         $query->whereHas('users', fn ($q) => $q->where('user_id', $user->id))
                             ->orWhere('user_id', $user->id);
                     })
                     ->get()
                     ->map(function ($challenge) use ($user) {
                         $participantPoints = $challenge->getParticipantPoints();
                         $challenge->users->each(function ($participant) use ($participantPoints, $challenge) {
                             $totalPoints = $participantPoints[$participant->id]['total_points'] ?? 0;
                             $participant->participant_points = $totalPoints;
                             $participant->completion_percentage = $challenge->points > 0
                                 ? round(($totalPoints / $challenge->points) * 100, 2)
                                 : 0;
                         });
                            // Add the authenticated user's completion_percentage directly to the challenge
                            $authUserPoints = $participantPoints[$user->id]['total_points'] ?? 0;
                            $challenge->user_completion_percentage = $challenge->points > 0
                                ? round(($authUserPoints / $challenge->points) * 100, 2)
                                : 0;

                         return $challenge;
                     })
                 : [],
             'userLevel' => fn () => $user?->level()->first() ?? [],
             'badges' => fn () => $user?->badges ?? [],
             'tasksAheadOfDeadline' => fn () => $user?->tasksAheadOfDeadline() ?? [],
         ];
     }


}
