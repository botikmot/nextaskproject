<?php

namespace App\Services;

use App\Models\Level;
use App\Models\User;
use App\Models\Badge;
use App\Models\Project;

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

    public function checkExplorerBadge(User $user)
    {
        $hasCreatedTask = $user->tasks()->exists();

        // Check if the user has joined at least one project
        $hasJoinedProject = Project::whereHas('users', function ($query) use ($user) {
            $query->where('users.id', $user->id); // Check if the user exists in the project's users
        })->exists();

        $hasSentMessage = $user->messages()->exists();

        if ($hasCreatedTask && $hasJoinedProject && $hasSentMessage) {
            $this->assignBadge($user, 'Explorer');
        }
    }

    public function checkAchieverBadge(User $user)
    {
        // Milestone 1: Completed Tasks
        $completedTasks = $user->tasks()
            ->whereHas('project', function ($query) {
                $query->whereColumn('tasks.status_id', 'projects.completed_status_id');
            })
            ->count();

        // Milestone 2: Completed Projects (using progress attribute)
        $completedProjects = $user->projects()
            ->where(function ($query) {
                $query->where('status', 'complete') // Use the 'status' column if available
                    ->orWhereRaw('progress = 100'); // Use progress attribute to determine completion
            })
            ->count();

        // Milestone 3: Challenge Points
        $challengePoints = $user->challenges()->sum('points'); // Sum points from challenges

        // Define thresholds for milestones
        $taskMilestone = 25; // Example: Complete 100 tasks
        $projectMilestone = 1; // Example: Complete 10 projects
        $challengeMilestone = 10; // Example: Earn 500 points from challenges

        // Check if user qualifies for the badge
        if (
            $completedTasks >= $taskMilestone &&
            $completedProjects >= $projectMilestone &&
            $challengePoints >= $challengeMilestone
        ) {
            $this->assignBadge($user, 'Achiever'); // Award the 'Achiever' badge
        }
    }

}
