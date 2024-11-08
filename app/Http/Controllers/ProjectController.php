<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProjectController extends Controller
{
    // Show the project index page
    public function index()
    {
        $projects = Project::with('members')->where('user_id', Auth::id())->get();

        return redirect()->back()->with([
            'success' => true,
            'message' => 'successfully fetched',
            'projects' => $projects,
        ]);
    }

    // Store a new project
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project = Project::create([
            'user_id' => Auth::id(), // Automatically set the admin to the creator
            'title' => $request->title,
            'description' => $request->description,
        ]);

        //return redirect()->route('projects.index')->with('success', 'Project created successfully!');
        return redirect()->back()->with([
            'success' => true,
            'message' => 'Project created successfully',
        ]);
    }

     // Show a specific project
    public function show($id)
    {  
        $project = Project::with(['statuses.tasks' => function ($query) {
            $query->orderBy('index')->with('users', 'comments.user'); // Order tasks by index
        }, 'members.user'])->findOrFail($id);

        return Inertia::render('Projects/Board', [
            'project' => $project,
        ]);
    }

    public function addMember(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        // Check if the authenticated user is the project owner
        if ($project->user_id == auth()->id()) {

            $memberIds = collect($request->members)->pluck('id')->toArray();
            $request->validate([
                'members' => 'required|array',
                'members.*.id' => 'exists:users,id', 
            ]);
      
            // Loop through each user ID and create a new ProjectUser entry
            foreach ($memberIds as $userId) {
                ProjectUser::updateOrCreate(
                    [
                        'project_id' => $project->id,
                        'user_id' => $userId,
                    ]
                );
            }
        
            return redirect()->back()->with([
                'success' => true,
                'message' => 'Members added successfully',
            ]);

        } else {
            // Handle unauthorized access
            abort(403, 'Unauthorized access');
        }
    }

    public function searchMembers(Request $request)
    {
        $query = trim($request->input('query', ''));
        $projectId = $request->input('project_id');

        // If query is empty or less than 3 characters, return an empty response
        if (strlen($query) < 3) {
            return response()->json([]);
        }

        // Get IDs of users who are already members of this project
        $existingMemberIds = ProjectUser::where('project_id', $projectId)
                            ->pluck('user_id')
                            ->toArray();

        $results = User::where(function ($queryBuilder) use ($query) {
                $queryBuilder->where('name', 'LIKE', "%{$query}%")
                            ->orWhere('email', 'LIKE', "%{$query}%");
            })
            //->where('id', '!=', Auth::id()) // Exclude the authenticated user
            ->whereNotIn('id', array_merge([$request->user()->id], $existingMemberIds))
            ->take(10)
            ->get(['id', 'name', 'email']);

        return response()->json($results);
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);

        // Check if the project has associated tasks
        if ($project->statuses->flatMap->tasks->isNotEmpty()) {
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Cannot delete project because it is associated with tasks.',
            ]);
        }

        $project->delete();

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Project successfully deleted',
        ]);
    }

}
