<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;


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
    public function show(Request $request, $id)
    {  
        $user_id = $request->user_id ? $request->user_id : Auth::id();
        $roles = Role::all();
        if($request->filter == 'all'){
            $project = Project::with(['statuses' => function ($query) {
                    $query->orderBy('created_at', 'asc'); // Sorting statuses by created_at desc
                }, 'statuses.tasks' => function ($query) {
                $query->orderBy('index');
                $query->with(['dependencies', 'status', 'dependentTasks', 'users', 'subtasks', 'comments' => function ($query) {
                        $query->with('user', 'attachments'); 
                    }
                ]);
            }, 'tasks', 'users' => function ($query) use ($id) {
                $query->with(['roles' => function ($roleQuery) use ($id) {
                    $roleQuery->wherePivot('project_id', $id);
                }]);
            }])->findOrFail($id);

            // Calculate and append progress for each task
            foreach ($project->statuses as $status) {
                foreach ($status->tasks as $task) {
                    $task->progress = $task->progress; // Access the accessor
                }
            }

        }else{
            $project = Project::with(['statuses' => function ($query) {
                    $query->orderBy('created_at', 'asc'); // Sorting statuses by created_at desc
                }, 'statuses.tasks' => function ($query) use ($user_id) {
                $query->orderBy('index')
                      ->with(['dependencies', 'status', 'dependentTasks', 'users', 'subtasks', 'comments' => function ($query) {
                          $query->with('user', 'attachments'); 
                      }])
                      ->where(function ($query) use ($user_id) {
                          $query->where('user_id', $user_id) // Tasks created by the authenticated user
                                ->orWhereHas('users', function ($userQuery) use ($user_id) {
                                    $userQuery->where('user_id', $user_id); // Tasks assigned to the authenticated user
                                });
                      });
            }, 'tasks', 'users' => function ($query) use ($id) {
                $query->with(['roles' => function ($roleQuery) use ($id) {
                    $roleQuery->wherePivot('project_id', $id);
                }]);
            }])->findOrFail($id);

            // Calculate and append progress for each task
            foreach ($project->statuses as $status) {
                foreach ($status->tasks as $task) {
                    $task->progress = $task->progress; // Access the accessor
                }
            }

        }

        $project->progress = $project->progress;
        $user = Auth::user();
        $userRole = $user->mainRoles->pluck('name')->first();
        return Inertia::render('Projects/Board', [
            'project' => $project,
            'roles' => $roles,
            'userRole' => $userRole,
        ]);
    }

    public function updateCompletedStatus(Request $request, $id)
    {
        $request->validate([
            'completed_status_id' => 'required|exists:statuses,id',
        ]);
        //dd($project);
        //$project->update(['completed_status_id' => $request->completed_status_id]);
        $project = Project::findOrFail($id);

        $project->completed_status_id = $request->completed_status_id;
        $project->save();

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Completed status updated successfully',
        ]);
    }

    public function addMember(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        // Default role ID (set it to the default you want, e.g., 2 for "member")
        $defaultRoleId = $roles = Role::where('name', 'member')->first();//'51149160-5ece-40d8-a6c0-b2010bede188';

        // Validate incoming request
        $request->validate([
            'members' => 'required|array', // Ensure that 'members' is an array
            'members.*.id' => 'required|exists:users,id', // Ensure each member has a valid user ID
            'members.*.role_id' => 'nullable|exists:roles,id', // Optionally, each member can have a role
        ]);

        // Loop through each member and attach them to the project with the specified or default role
        foreach ($request->members as $member) {
            $roleId = $member['role_id'] ?? $defaultRoleId->id; // Use the provided role or default to 2

            // Attach the user to the project with the specified role
            $project->users()->syncWithoutDetaching([
                $member['id'] => ['role_id' => $roleId]
            ]);
        }

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Members added successfully',
        ]);
    }

    public function searchMembers(Request $request)
    {
        $query = trim($request->input('query', ''));
        $projectId = $request->input('project_id');

        // If query is empty or less than 3 characters, return an empty response
        if (strlen($query) < 3) {
            return response()->json([]);
        }

        // Retrieve the project and its existing member IDs via the users relationship
        $project = Project::with('users')->findOrFail($projectId);
        $existingMemberIds = $project->users->pluck('id')->toArray();

        $results = User::where(function ($queryBuilder) use ($query) {
                $queryBuilder->where('name', 'LIKE', "%{$query}%")
                            ->orWhere('email', 'LIKE', "%{$query}%");
            })
            //->where('id', '!=', Auth::id()) // Exclude the authenticated user
            ->whereNotIn('id', array_merge($existingMemberIds))
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

    public function addUserRole(Request $request)
    {
        $project = Project::findOrFail($request->project_id);
        $user = User::findOrFail($request->user_id);
        $role = Role::findOrFail($request->role_id);

        // Check if the user already has a role in the project
        if ($project->users()->where('user_id', $user->id)->exists()) {
            // Update the role by detaching the current role first
            $project->users()->detach($user->id);
        }

        $project->users()->attach($user->id, ['role_id' => $role->id]);

        return redirect()->back()->with([
            'success' => true,
            'message' => 'User successfully added a role',
        ]);
    }

}
