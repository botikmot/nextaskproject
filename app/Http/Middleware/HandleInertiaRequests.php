<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

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
                    // Calculate mutual projects
                    $mutualProjectsCount = $friend->projectMemberships->pluck('id')
                        ->intersect($request->user()->projectMemberships->pluck('id'))
                        ->count();

                    // Add mutual projects count as a new attribute
                    $friend->mutual_projects = $mutualProjectsCount;

                    return $friend;
                })
                : [],
        ];
    }
}
