<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Subtask;
use App\Models\TaskComment;
use App\Models\Attachment;
use App\Models\Status;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use App\Models\Label;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Services\LevelService;

class TaskController extends Controller
{
    protected $levelService;

    public function __construct(LevelService $levelService)
    {
        $this->levelService = $levelService;
    }
    // create new task
    public function store(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project = Project::findOrFail($id);
        //dd($request->all());
        if (!$project->userHasPermission(auth()->user(), 'create_task')) {
            //return response()->json(['error' => 'Unauthorized'], 403);
            return redirect()->back()->with([
                'success' => false,
                'message' => 'You do not have permission to create task in this project.',
            ]);
        }

    
        $task = Task::create([
            'user_id' => Auth::id(), // Automatically set the admin to the creator
            'title' => $request->title,
            'description' => $request->description,
            'project_id' => $id,
            'status_id' => $request->status_id,
            'due_date' => $request->due_date,
            'priority' => $request->priority,
            'status' => $request->status,
            'index' => $request->index,
            'points' => $request->points,
        ]);

        if ($request->assigned_members && count($request->assigned_members) > 0) {
             // Attach users to the task
            //$task->users()->attach($request->assigned_members);
            $timestamp = now(); // Current timestamp
            $assignedMembersWithTimestamps = collect($request->assigned_members)
                ->mapWithKeys(fn ($id) => [$id => ['created_at' => $timestamp, 'updated_at' => $timestamp]])
                ->toArray();

            $task->users()->attach($assignedMembersWithTimestamps);
        }
       
        if ($request->labels && count($request->labels) > 0) {
            // Attach users to the task
            $task->labels()->attach($request->labels);
        }

        if (!empty($request->challenge_ids)) {
            $task->challenges()->sync($request->challenge_ids);
        }

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Task created successfully',
        ]);
    }


    public function getTasks(Request $request)
    {
         // Retrieve filters and default pagination size
        $selectedProjects = $request->input('selectedProjects', []);
        $selectedStatuses = $request->input('selectedStatuses', []);
        $sortBy = $request->input('sortBy', 'created_at'); // Default to 'created_at'
        $sortOrder = $request->input('sortOrder', 'asc');  // Default to 'asc'
        $perPage = 8;
        $user = Auth::user();

        $userRole = $user->mainRoles->pluck('name')->first();
        $projects = Project::where('user_id', auth()->id()) // Check if the user is the creator
            ->orWhereHas('users', function ($query) {
                $query->where('user_id', auth()->id()); // Check if the user is a member
            })
            //->with('members')
            ->with(['users', 'statuses.tasks'])
            ->orderBy('created_at', 'desc')
            ->get()->append('progress');

        $tasks = $user->tasks()->with([
            'histories.user',
            'histories.oldStatus',
            'histories.newStatus',
            'dependencies.status',
            'labels',
            'project.users',
            'project.tasks',
            'project.statuses',
            'project.labels',
            'users',
            'status',
            'challenges',
            'subtasks' => function ($subtaskQuery) {
                $subtaskQuery->orderBy('created_at', 'asc');
            },
            'comments' => function ($query) {
                $query->with('user', 'attachments'); 
            }
        ])
        ->when(!empty($selectedProjects), function ($query) use ($selectedProjects) {
            $query->whereIn('project_id', $selectedProjects);
        })
        ->when(!empty($selectedStatuses), function ($query) use ($selectedStatuses) {
            $query->whereIn('status_id', $selectedStatuses);
        })
        ->when($sortBy === 'due_date', function ($query) use ($sortOrder) {
            $query->orderByRaw("
                CASE
                    WHEN due_date >= CURDATE() THEN 0 -- Upcoming due dates
                    WHEN due_date < CURDATE() THEN 1 -- Past due dates
                    ELSE 2 -- NULL due dates
                END,
                ABS(TIMESTAMPDIFF(SECOND, NOW(), due_date)) $sortOrder
            ");
        }, function ($query) use ($sortBy, $sortOrder) {
            $query->orderBy($sortBy, $sortOrder);
        })
        //->orderBy($sortBy, $sortOrder)
        ->paginate($perPage);

        //return response()->json($tasks);
        return Inertia::render('Tasks/MyTasks', [
            'tasks' => $tasks,
            'projects' => $projects,
            'userRole' => $userRole,
            'selectedProjects' => $selectedProjects,
            'selectedStatuses' => $selectedStatuses,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
        ]);

    }


    public function getTasksProject()
    {
        $projects = Project::where('user_id', auth()->id()) // Check if the user is the creator
            ->orWhereHas('users', function ($query) {
                $query->where('user_id', auth()->id()); // Check if the user is a member
            })
            ->with(['labels', 'users', 'statuses' => function ($query) {
                $query->orderBy('created_at', 'asc');
            }, 'statuses.tasks'])
            ->orderBy('created_at', 'desc')
            ->get()->append('progress');

        //$labels = Label::all();

        return response()->json([
                'success' => true,
                'projects' => $projects,
                //'labels' => $labels,
            ]);
    }

    public function updateTask(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $taskData = Task::find($id);

        $project = Project::findOrFail($taskData->project_id);
        if (!$project->userHasPermission(auth()->user(), 'update_task')) {
            return redirect()->back()->with([
                'success' => false,
                'message' => 'You do not have permission to update task in this project.',
            ]);
        }

        $taskData->title = $request->title;
        $taskData->due_date = $request->due_date;
        $taskData->priority = $request->priority;
        $taskData->status_id = $request->status;
        $taskData->points = $request->points;
        $taskData->save();

        if (!empty($request->challenge_ids)) {
            $taskData->challenges()->sync($request->challenge_ids);
        }


        if($project->completed_status_id == $request->status){
            $user = auth()->user();
            if (!empty($request->challenge_ids)) {
                foreach ($request->challenge_ids as $challengeId) {
                    // Get the current progress for the user on the specific challenge
                    $currentProgress = $user->challenges()
                    ->where('challenge_id', $challengeId)
                    ->first()
                    ->pivot
                    ->progress ?? 0;

                    // Add the new points to the current progress
                    $updatedProgress = $currentProgress + $request->points;

                    // Update the pivot table with the new progress
                    $user->challenges()->updateExistingPivot($challengeId, ['progress' => $updatedProgress]);

                }
            }

            $this->levelService->checkAndUpdateLevel($user);
            $this->levelService->checkAllBadges($user);
        }



        if ($request->labels && count($request->labels) > 0) {
            $taskData->labels()->detach();
            $taskData->labels()->attach($request->labels);
        }

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Task updated successfully',
        ]);

    }

    public function updateTaskStatus(Request $request, $id)
    {
        // Find the status based on ID
        $status = Status::find($id);
        if (!$status) {
            return response()->json(['error' => 'Status not found'], 404);
        }

        $project = Project::findOrFail($request->input('project_id'));

        if (!$project->userHasPermission(auth()->user(), 'update_task')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
                
        $tasksData = $request->input('tasks');

        $result = $this->canStartTask($request->input('task_id'));
        if(!$result['canStart']){
            return response()->json([
                'success' => false,
                'message' => "Dependency task <span style='color: red;'>'{$result['dependencyName']}'</span> is not yet completed.",
            ]);
        }
        
        $user = auth()->user();
        foreach($tasksData as $data){
            $taskData = Task::find($data['id']);
            $taskData->index = $data['index'];
            $taskData->status_id = $data['status_id'];
            $taskData->save();
            
            if($project->completed_status_id == $data['status_id'] && $data['id'] == $request->input('task_id')){
                
                if (!empty($data['challenges'])) {
                    foreach ($data['challenges'] as $challengeId) {
                        // Get the current progress for the user on the specific challenge
                        $currentProgress = $user->challenges()
                        ->where('challenge_id', $challengeId)
                        ->first()
                        ->pivot
                        ->progress ?? 0;
                        // Add the new points to the current progress
                        $updatedProgress = $currentProgress + $data['points'];
                        
                        // Update the pivot table with the new progress
                        $user->challenges()->updateExistingPivot($challengeId, ['progress' => $updatedProgress]);
    
                    }
                }

                $this->levelService->checkAndUpdateLevel($user);
                $this->levelService->checkAllBadges($user);

            }

        }
       
        return response()->json([
            'success' => true,
            'message' => 'Tasks updated successfully',
        ]);

    }

    // Delete a task
    public function destroy($id)
    {
        $task = Task::findOrFail($id);

        // Check if the task has associated users
        if ($task->comments()->exists() || $task->users()->exists()) {
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Task cannot be deleted because it has related data.',
            ]);
        }

        $task->delete();

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Task successfully deleted',
        ]);
    }

    public function assignMembers(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $project = Project::findOrFail($task->project_id);

        if (!$project->userHasPermission(auth()->user(), 'update_task')) {
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Unauthorized.',
            ]);
        }

        if ($request->assigned_members && count($request->assigned_members) > 0) {
            // Attach users to the task
           //$task->users()->attach($request->assigned_members);
           // Attach users to the task with timestamps
            $timestamp = now(); // Current timestamp
            $assignedMembersWithTimestamps = collect($request->assigned_members)
                ->mapWithKeys(fn ($id) => [$id => ['created_at' => $timestamp, 'updated_at' => $timestamp]])
                ->toArray();

            $task->users()->attach($assignedMembersWithTimestamps);
        }

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Users successfully assigned.',
        ]);

    }

    public function removeMembers(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        if ($request->removed_users && count($request->removed_users) > 0) {
            // Detach users to the task
           $task->users()->detach($request->removed_users);
        }

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Users successfully removed.',
        ]);

    }

    public function taskComment(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $request->validate([
            'comment' => 'required|string',
            'attachments.*' => 'nullable|mimes:jpg,png,pdf,doc,docx|max:2048'
        ]);

        $comment = new TaskComment([
            'content' => $request->comment,
            'user_id' => auth()->id(),
        ]);

        $task->comments()->save($comment);

        // Check if there are attachments to upload
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                // Store the file and get the path
                $path = $file->store('task_attachments', 'public');

                // Create a new Attachment model and associate it with the comment
                $attachment = new Attachment([
                    'path' => $path,
                    'filename' => $file->getClientOriginalName(),
                ]);

                $comment->attachments()->save($attachment);
            }

            $comment->load('attachments', 'user');

            return response()->json([
                'success' => true,
                'message' => 'Comment successfully saved.',
                'comment' => $comment, // Return comment with attachments
            ]);

        }


        return redirect()->back()->with([
            'success' => true,
            'message' => 'Comment successfully saved.',
        ]);

    }

    public function removeComment($id)
    {
        // Find the comment by ID
        $comment = TaskComment::findOrFail($id);
        // Delete the comment
        $comment->delete();
        return redirect()->back()->with([
            'success' => true,
            'message' => 'Comment successfully deleted.',
        ]);
    }

    public function updateComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string',
            //'attachments.*' => 'nullable|max:5048'
        ]);

        $comment = TaskComment::findOrFail($id);

        $comment->content = $request->comment;
        $comment->save();

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Comment successfully updated.',
        ]);

    }


    public function createSubtask(Request $request, $taskId)
    {
        $request->validate([
            'subtask' => 'required|string|max:255',
        ]);

        $parentTask = Task::findOrFail($taskId);

        $project = Project::findOrFail($parentTask->project_id);

        if (!$project->userHasPermission(auth()->user(), 'update_task')) {
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Unauthorized.',
            ]);
        }

        $subtask = new Subtask([
            'name' => $request->subtask,
        ]);

        $parentTask->subtasks()->save($subtask);

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Subtask successfully added.',
        ]);

    }

    public function updateSubtask(Request $request, $subtaskId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        
        $subtask = Subtask::findOrFail($subtaskId);
        //$this->authorize('update', $subtask); // Ensure user is authorized to update
        $parentTask = Task::findOrFail($subtask->parent_id);

        $project = Project::findOrFail($parentTask->project_id);

        if (!$project->userHasPermission(auth()->user(), 'update_task')) {
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Unauthorized.',
            ]);
        }



        $subtask->name = $request->name;
        $subtask->is_completed = $request->is_completed;
        $subtask->save();

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Subtask successfully updated.',
        ]);

    }

    public function removeSubtask($subtaskId)
    {
        $subtask = Subtask::findOrFail($subtaskId);

        $parentTask = Task::findOrFail($subtask->parent_id);

        $project = Project::findOrFail($parentTask->project_id);

        if (!$project->userHasPermission(auth()->user(), 'update_task')) {
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Unauthorized.',
            ]);
        }

        $subtask->delete();
        return redirect()->back()->with([
            'success' => true,
            'message' => 'Subtask successfully deleted.',
        ]);
    }



    public function setDependency(Request $request, $taskId)
    {
        // Add dependency
        $task = Task::find($taskId);

        $project = Project::findOrFail($task->project_id);

        if (!$project->userHasPermission(auth()->user(), 'update_task')) {
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Unauthorized.',
            ]);
            //return response()->json(['error' => 'Unauthorized'], 403);
        }


        $task->dependencies()->syncWithoutDetaching($request->dependency);

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Dependency added successfully.',
        ]);
    }

    public function canStartTask($taskId)
    {
        $task = Task::with('dependencies')->findOrFail($taskId);
        foreach ($task->dependencies as $dependency) {
            // Get the completed status ID for the project this task belongs to
            $completedStatusId = $dependency->project->completed_status_id ?? $dependency->project->statuses()->orderByDesc('created_at')->first()?->id;

            // Check if the dependency task is in the completed status
            if ($dependency->status_id !== $completedStatusId) {
                return [
                    'canStart' => false,
                    'dependencyName' => $dependency->title ?? 'Unnamed Task',
                ];
            }
        }

        return [
            'canStart' => true,
        ];

    }

    public function storeLabel(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:255',
        ]);

        $project = Project::find($id);

        $label = new Label([
            'name' => $request->name,
            'color' => $request->color
        ]);

        $project->labels()->save($label);
       
        return redirect()->back()->with([
            'success' => true,
            'message' => 'Label added successfully.',
        ]);
    }

    public function updateLabel(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $label = Label::findOrFail($id);
        $label->name = $request->name;
        $label->color = $request->color;
        $label->save();

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Label successfully updated.',
        ]);
    }

    public function removeLabel($id)
    {
        $label = Label::findOrFail($id);

        $label->delete();
        return redirect()->back()->with([
            'success' => true,
            'message' => 'Label successfully deleted.',
        ]);
    }


    public function removeDependency(Request $request, $taskId)
    {
        $dependsOnTaskId = $request->depends_on_task_id;
        // Validate that the dependency exists
        $task = Task::findOrFail($taskId);
        $dependencyExists = $task->dependencies()->where('depends_on_task_id', $dependsOnTaskId)->exists();

        if (!$dependencyExists) {
            return response()->json([
                'success' => false,
                'message' => 'The specified dependency does not exist.',
            ], 404);
        }
        // Remove the dependency
        $task->dependencies()->detach($dependsOnTaskId);
        return redirect()->back()->with([
            'success' => true,
            'message' => 'Dependency removed successfully.',
        ]);
    }

    public function updateTaskDescription(Request $request, $taskId)
    {
        $request->validate([
            'description' => 'required',
        ]);

        $task = Task::findOrFail($taskId);

        $task->description = $request->description;
        $task->save();

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Task updated successfully',
        ]);

    }

    public function getTaskCompletionRate(Request $request)
    {
        $userId = auth()->id(); // Get the authenticated user ID

        // Get total tasks assigned to the user this week
        $totalTasksThisWeek = Task::assignedThisWeek($userId)->count();

        // Get completed tasks for the user this week
        $tasksCompletedThisWeek = Task::completedThisWeek($userId)->count();

        // Calculate task completion rate
        $taskCompletionRate = $totalTasksThisWeek > 0 
            ? round(($tasksCompletedThisWeek / $totalTasksThisWeek) * 100) 
            : 0;

        $tasksDueToday = Task::dueToday()->count();
        $dueToday = Task::dueToday()->get();
        $today = Carbon::now()->setTimezone('Asia/Manila');//->startOfDay();
        return response()->json([
            'success' => true,
            'totalTasksThisWeek' => $totalTasksThisWeek,
            'tasksCompletedThisWeek' => $tasksCompletedThisWeek,
            'taskCompletionRate' => $taskCompletionRate,
            'tasksDueToday' => $tasksDueToday,
            'todayIs' => $today->format('Y-m-d H:i:s'),
            'dueToday' => $dueToday,
        ]);
    }


    public function startTimer(Task $task)
    {
        // Check if a timer is already running
        if ($task->start_time) {
            return response()->json([
                'message' => 'Timer is already running for this task.',
            ], 400);
        }
        // Start the timer
        $task->start_time = now();
        $task->save(); 
        
        return response()->json([
            'message' => 'Timer started successfully.',
            'startTime' => Carbon::parse($task->start_time)->toIso8601String(),
        ]);
    }

    public function stopTimer(Task $task)
    {
        // Check if the timer was started
        if (!$task->start_time) {
            return response()->json([
                'message' => 'Timer has not been started yet.',
            ], 400);
        }

        // Stop the timer and calculate duration
        $stopTime = now();
        $startTime = Carbon::parse($task->start_time); // Convert start_time to Carbon
        $duration = $startTime->diffInMinutes($stopTime);
        $durationInSeconds = $startTime->diffInSeconds($stopTime);
        $durationInHours = $duration / 60;

        // Update the task
        $task->stop_time = now();
        $task->total_tracked_minutes = $task->total_tracked_minutes + $duration;
        $task->total_tracked_seconds += $durationInSeconds;
        $task->start_time = null;
        $task->save();

        return response()->json([
            'message' => 'Timer stopped successfully.',
            'tracked_minutes' => $duration,
            'tracked_seconds' => $durationInSeconds,
            'tracked_hours' => $durationInHours,
        ]);
    }


    public function getTaskTime(Task $task)
    {
        $totalTrackedSeconds = $task->total_tracked_seconds ?? 0;
        // Calculate hours, minutes, and seconds from total_tracked_minutes
        
        // If the timer is currently running, add the elapsed time to total_tracked_seconds
        if (!is_null($task->start_time)) {
            // Parse start_time in UTC and compare with the current UTC time
            $startTime = Carbon::parse($task->start_time)->setTimezone('UTC');
            $now = Carbon::now('UTC'); // Always use UTC for now
            $elapsedSeconds = $now->diffInSeconds($startTime, false); // Add 'false' to allow negative results for debugging
            // Avoid negative elapsedSeconds by validating the timestamp
            if ($elapsedSeconds >= 0) {
                $totalTrackedSeconds += $elapsedSeconds;
            } 
        }

        // Calculate hours and minutes from total_tracked_minutes
        $hours = floor($totalTrackedSeconds / 3600);
        $minutes = floor(($totalTrackedSeconds % 3600) / 60);
        $seconds = $totalTrackedSeconds % 60;

        return response()->json([
            'hours' => $hours,
            'minutes' => $minutes,
            'seconds' => $seconds,
            'totalTrackedSeconds' => $totalTrackedSeconds,
            'isRunning' => !is_null($task->start_time), // To check if the timer is currently running
            'startTime' => $task->start_time ? Carbon::parse($task->start_time)->toIso8601String() : null, // Add start_time
        ]);
    }

}
