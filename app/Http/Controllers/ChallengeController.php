<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Challenge;
use Inertia\Inertia;

class ChallengeController extends Controller
{
    public function index()
    {
        $authUser = auth()->user();
        $challenges = Challenge::with('rewards', 'users')
            ->where(function ($query) use ($authUser) {
                // Challenges created by friends
                $friendIds = $authUser->friends()->pluck('id');
            
                // Challenges created by project members
                $projectMemberIds = $authUser->projectMemberships()
                    ->with('users')
                    ->get()
                    ->pluck('users')
                    ->flatten()
                    ->pluck('id');

                // Combine all relevant user IDs
                $allowedUserIds = $friendIds->merge($projectMemberIds)->unique();

                // Filter challenges where `user_id` matches any of the allowed IDs
                $query->whereIn('user_id', $allowedUserIds)->orWhere('user_id', $authUser->id);
            })
            ->get()
            ->map(function ($challenge) use ($authUser) {
                // Determine if the user has joined this challenge
                $joinedChallengeIds = $authUser->challenges()->pluck('challenges.id')->toArray();
                $challenge->isJoined = in_array($challenge->id, $joinedChallengeIds);
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
                
            });

        return Inertia::render('Challenge/Dashboard', [
            'challenges' => $challenges,
        ]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_team_based' => 'boolean',
            'points' => 'required|integer|min:0',
        ]);

        $challenge = Challenge::create([
            'name' => $request->name,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_team_based' => $request->is_team_based,
            'points' => $request->points,
            'user_id' => auth()->id(),
        ]);

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Challenge created successfully!',
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_team_based' => 'boolean',
            'points' => 'required|integer|min:0',
        ]);

        $challenge = Challenge::findOrFail($id);
        $challenge->name = $request->name;
        $challenge->description = $request->description;
        $challenge->start_date = $request->start_date;
        $challenge->end_date = $request->end_date;
        $challenge->is_team_based = $request->is_team_based;
        $challenge->points = $request->points;
        $challenge->save();

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Challenge successfully updated',
        ]);

    }

    public function destroy($id)
    {
        $challenge = Challenge::findOrFail($id);

        // Check if the challenge has associated users
        if ($challenge->users->isNotEmpty()) {
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Cannot delete challenge because it is associated with users.',
            ]);
        }

        $challenge->delete();

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Challenge successfully deleted',
        ]);
    }

    public function join(Challenge $challenge)
    {
        $user = auth()->user();

        if ($challenge->users()->where('user_id', $user->id)->exists()) {
            return redirect()->back()->with([
                'success' => false,
                'message' => 'You have already joined this challenge.',
            ]);
        }

        $challenge->users()->attach($user->id);
        return redirect()->back()->with([
            'success' => true,
            'message' => 'Challenge joined successfully!',
        ]);
    }

    public function addReward(Request $request, $challengeId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'points_required' => 'required|integer|min:0',
        ]);

        $challenge = Challenge::findOrFail($challengeId);

        $reward = $challenge->rewards()->create([
            'name' => $request->name,
            'description' => $request->description,
            'points_required' => $request->points_required,
        ]);

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Reward added successfully!',
        ]);

    }

    public function viewProgress(Challenge $challenge)
    {
        $user = auth()->user();

        // Get tasks for the user in the given challenge
        $userTasks = $challenge->tasks()
            ->whereHas('users', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->with('status', 'project')
            ->get();

        return response()->json($userTasks);

    }

    public function leaveChallenge(Request $request, $challengeId)
    {
        $user = $request->user();

        // Find the challenge by ID
        $challenge = Challenge::findOrFail($challengeId);

        // Check if the user is part of the challenge
        if (!$challenge->users()->where('users.id', $user->id)->exists()) {
            return redirect()->back()->with([
                'success' => false,
                'message' => 'You are not a participant in this challenge.',
            ]);
        }

        // Remove the user from the challenge
        $challenge->users()->detach($user->id);

        return redirect()->back()->with([
            'success' => true,
            'message' => 'You have successfully left the challenge.',
        ]);
    }


}
