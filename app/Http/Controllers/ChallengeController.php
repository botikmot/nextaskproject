<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Challenge;

class ChallengeController extends Controller
{
    public function index()
    {
        $challenges = Challenge::with('rewards', 'users')->get();
        return response()->json($challenges);
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
            /* return response()->json([
                'message' => 'You are not a participant in this challenge.',
            ], 400); */
        }

        // Remove the user from the challenge
        $challenge->users()->detach($user->id);

        return redirect()->back()->with([
            'success' => true,
            'message' => 'You have successfully left the challenge.',
        ]);
        /* return response()->json([
            'message' => 'You have successfully left the challenge.',
        ], 200); */
    }


}
