<?php

namespace App\Services;

use App\Models\Level;
use App\Models\User;
use App\Models\Badge;

class LevelService
{
    public function checkAndUpdateLevel(User $user)
    {
        $currentPoints = $user->totalPoints();

        $newLevel = Level::where('points_required', '<=', $currentPoints)
            ->orderBy('points_required', 'desc')
            ->first();

        if ($newLevel && $user->level_id !== $newLevel->id) {
            $user->level_id = $newLevel->id;
            $user->save();
        }
    }

    public function assignBadge(User $user, $badgeName)
    {
        $badge = Badge::where('name', $badgeName)->first();

        if (!$badge || $user->badges->contains($badge)) {
            return; // Badge doesn't exist or is already earned
        }

        $user->badges()->attach($badge);
    }

    public function removeBadge(User $user, $badgeName)
    {
        $badge = Badge::where('name', $badgeName)->first();

        if ($badge && $user->badges->contains($badge->id)) {
            $user->badges()->detach($badge->id);
        }
    }

    public function checkTaskBadges(User $user)
    {
        $completedTasks = $user->tasks()->whereHas('project', function ($query) {
                                $query->whereColumn('tasks.status_id', 'projects.completed_status_id');
                            })->count();

        if ($completedTasks >= 1) {
            $this->assignBadge($user, 'Task Starter');
        }
        if ($completedTasks >= 50) {
            $this->removeBadge($user, 'Task Starter');
            $this->assignBadge($user, 'Task Master');
        }

        // Award Task Ninja for completing tasks ahead of deadlines
        $tasksAheadOfDeadline = $user->tasks()
            ->whereHas('project', function ($query) {
                $query->whereColumn('tasks.status_id', 'projects.completed_status_id');
            })
            ->whereColumn('tasks.updated_at', '<', 'tasks.due_date') // Compare updated_at to deadline
            ->count();

        if ($tasksAheadOfDeadline >= 10) {
            $this->removeBadge($user, 'Task Starter');
            $this->removeBadge($user, 'Task Master');
            $this->assignBadge($user, 'Task Ninja');
        }
    }


}
