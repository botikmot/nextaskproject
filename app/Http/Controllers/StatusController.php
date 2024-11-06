<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Status;
use Illuminate\Support\Facades\Auth;

class StatusController extends Controller
{
    // create a new status
    public function store(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            //'description' => 'nullable|string',
        ]);

        $status = Status::create([
            'user_id' => Auth::id(), // Automatically set the admin to the creator
            'name' => $request->name,
            'project_id' => $id,
        ]);

        //return redirect()->route('projects.index')->with('success', 'Project created successfully!');
        return redirect()->back()->with([
            'success' => true,
            'message' => 'Project created successfully',
        ]);
    }

    // Delete a status
    public function destroy($id)
    {
        $status = Status::findOrFail($id);

        // Check if the status has associated tasks
        if ($status->tasks()->exists()) {
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Cannot delete status because it is associated with tasks.',
            ]);
        }

        $status->delete();

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Status successfully deleted',
        ]);
    }
}
