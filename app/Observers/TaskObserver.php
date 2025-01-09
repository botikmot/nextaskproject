<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\TaskHistory;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        $changes = $task->getChanges();

        // Remove 'updated_at' and 'index' from the changes to be logged
        unset($changes['updated_at'], $changes['index'], $changes['start_time'], $changes['stop_time'], $changes['total_tracked_seconds'], $changes['total_tracked_minutes']);

        // If no changes remain, exit early
        if (empty($changes)) {
            return;
        }

        foreach ($changes as $attribute => $newValue) {
            if ($attribute === 'updated_at') continue;

            TaskHistory::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'attribute' => $attribute,
                'old_value' => $task->getOriginal($attribute),
                'new_value' => $newValue,
            ]);
        }
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "restored" event.
     */
    public function restored(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "force deleted" event.
     */
    public function forceDeleted(Task $task): void
    {
        //
    }
}
