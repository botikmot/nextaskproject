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

class TaskController extends Controller
{
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
        ]);

        if ($request->assigned_members && count($request->assigned_members) > 0) {
             // Attach users to the task
            $task->users()->attach($request->assigned_members);
        }
       
        if ($request->labels && count($request->labels) > 0) {
            // Attach users to the task
            $task->labels()->attach($request->labels);
       }

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Task created successfully',
        ]);
    }

    public function getTasks()
    {
        $projects = Project::where('user_id', auth()->id()) // Check if the user is the creator
            ->orWhereHas('users', function ($query) {
                $query->where('user_id', auth()->id()); // Check if the user is a member
            })
            ->with(['users', 'statuses' => function ($query) {
                $query->orderBy('created_at', 'asc');
            }, 'statuses.tasks'])
            ->orderBy('created_at', 'desc')
            ->get()->append('progress');

        $labels = Label::all();

        return response()->json([
                'success' => true,
                'projects' => $projects,
                'labels' => $labels,
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
        $taskData->save();

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

        foreach($tasksData as $data){
            $taskData = Task::find($data['id']);
            $taskData->index = $data['index'];
            $taskData->status_id = $data['status_id'];
            $taskData->save();
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
           $task->users()->attach($request->assigned_members);
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



}
