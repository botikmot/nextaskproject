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

}
