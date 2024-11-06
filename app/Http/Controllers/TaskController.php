<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Status;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    // create new task
    public function store(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
    
        $task = Task::create([
            'user_id' => Auth::id(), // Automatically set the admin to the creator
            'title' => $request->title,
            'description' => $request->description,
            'project_id' => $id,
            'status_id' => $request->status_id,
            'due_date' => $request->due_date,
            'priority' => $request->priority,
            'status' => $request->status,
            'labels' => $request->labels,
            'index' => 0,
        ]);

        if ($request->assigned_members && count($request->assigned_members) > 0) {
             // Attach users to the task
            $task->users()->attach($request->assigned_members);
        }
       


        //return redirect()->route('projects.index')->with('success', 'Project created successfully!');
        return redirect()->back()->with([
            'success' => true,
            'message' => 'Project created successfully',
        ]);
    }

    public function updateTask(Request $request, $id)
    {
        // Find the status based on ID
        $status = Status::find($id);
        if (!$status) {
            return response()->json(['error' => 'Status not found'], 404);
        }
                
        $tasksData = $request->input('tasks');
        foreach($tasksData as $data){
            $taskData = Task::find($data['id']);
            $taskData->index = $data['index'];
            $taskData->status_id = $data['status_id'];
            $taskData->save();
        }
       
        return redirect()->back()->with([
            'success' => true,
            'message' => 'Tasks updated successfully',
        ]);

    }

    // Delete a status
    public function destroy($id)
    {
        $task = Task::findOrFail($id);

        // Check if the task has associated users
        /* if ($task->users()->exists()) {
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Cannot delete task because it is associated with users.',
            ]);
        } */

        $task->delete();

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Task successfully deleted',
        ]);
    }

}
