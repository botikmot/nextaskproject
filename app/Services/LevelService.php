<?php

namespace App\Services;

use App\Models\Level;
use App\Models\User;
use App\Models\Badge;
use App\Models\Project;
use App\Models\Challenge;

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

    // Centralized method to check all badges
    public function checkAllBadges(User $user)
    {
        $badgeCheckMethods = [
            'checkTaskBadges',
            'checkExplorerBadge',
            'checkAchieverBadge',
            'checkSocialStarBadge',
            'checkTeamPlayerBadge',
            'checkProjectLeaderBadge',
            'checkMultitaskerBadge',
            'checkConversationBadge',
            'checkConnectorBadge',
            'checkChallengeBadge',
        ];

        foreach ($badgeCheckMethods as $method) {
            if (method_exists($this, $method)) {
                $this->$method($user);
            }
        }
    }

    // Helper method to assign or remove a badge
    protected function updateBadge(User $user, string $badgeName, bool $assign = true)
    {
        $badge = Badge::where('name', $badgeName)->first();

        if (!$badge) {
            return; // Badge doesn't exist
        }

        if ($assign && !$user->badges->contains($badge)) {
            $user->badges()->attach($badge);
        } elseif (!$assign && $user->badges->contains($badge)) {
            $user->badges()->detach($badge);
        }
    }

    // Badge: Task-related badges
    public function checkTaskBadges(User $user)
    {
        $completedTasks = $user->tasks()->whereHas('project', function ($query) {
            $query->whereColumn('tasks.status_id', 'projects.completed_status_id');
        })->count();

        $this->updateBadge($user, 'Task Starter', $completedTasks >= 1);
        $this->updateBadge($user, 'Task Master', $completedTasks >= 50);
        $this->updateBadge($user, 'Task Ninja', $completedTasks >= 10);
    }

    // Badge: Explorer
    public function checkExplorerBadge(User $user)
    {
        $hasCreatedTask = $user->tasks()->exists();
        $hasJoinedProject = Project::whereHas('users', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })->exists();
        $hasSentMessage = $user->messages()->exists();

        $this->updateBadge($user, 'Explorer', $hasCreatedTask && $hasJoinedProject && $hasSentMessage);
    }

    // Badge: Achiever
    public function checkAchieverBadge(User $user)
    {
        $completedTasks = $user->tasks()->whereHas('project', function ($query) {
            $query->whereColumn('tasks.status_id', 'projects.completed_status_id');
        })->count();

        $completedProjects = $user->projects()
            ->where('status', 'complete')
            ->get()
            ->filter(fn($project) => $project->progress === 100)
            ->count();

        $completedChallenges = Challenge::with('users')->get()->filter(function ($challenge) use ($user) {
            return $challenge->isCompletedBy($user);
        })->count();

        $this->updateBadge($user, 'Achiever', $completedTasks >= 50 && $completedProjects >= 1 && $completedChallenges >= 5);
    }

    // Badge: Social Star
    public function checkSocialStarBadge(User $user)
    {
        $totalInteractions = $user->posts()->count() + $user->conversations()->count();
        $this->updateBadge($user, 'Social Star', $totalInteractions >= 50);
    }

    // Check and award the "Team Player" badge
    public function checkTeamPlayerBadge(User $user)
    {
        // Count the number of projects the user is part of
        $collaboratedProjectsCount = Project::whereHas('users', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })->count();

        // Award the badge if the user has collaborated on 5 or more projects
        $this->updateBadge($user, 'Team Player', $collaboratedProjectsCount >= 5);
    }

    public function checkProjectLeaderBadge(User $user)
    {
        // Check the number of projects created by the user with 100% progress
        $projectsCreated = Project::where('user_id', $user->id)
            ->get()
            ->filter(fn($project) => $project->progress === 100)
            ->count();

        // Award the badge if the user has successfully created and completed at least 1 project
        $this->updateBadge($user, 'Project Leader', $projectsCreated >= 1);
    }

    public function checkMultitaskerBadge(User $user)
    {
        // Fetch all projects where the user is contributing
        $projects = Project::whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get();

        // Filter projects where progress is less than 100
        $activeProjects = $projects->filter(function ($project) {
            return $project->progress < 100; // Use the accessor here
        })->count();

        // Award the badge if the user is contributing to more than 3 active projects
        $this->updateBadge($user, 'Multitasker', $activeProjects > 3);
    }

    // Badge: Conversation-related badges
    public function checkConversationBadge(User $user)
    {
        $totalConversations = $user->conversations()->count();

        $this->updateBadge($user, 'Conversation Starter', $totalConversations >= 1);
        $this->updateBadge($user, 'Chatterbox', $totalConversations > 100);
    }

    public function checkConnectorBadge(User $user)
    {
        // Count group chats started by the user (where they are an admin)
        $groupChatsStarted = $user->conversations()
            ->where('type', 'group') // Ensure it's a group chat
            //->wherePivot('is_admin', true) // Ensure the user is an admin
            ->whereHas('users', function($query) use ($user) {
                $query->where('user_id', $user->id); // Check if the user is a member
            })
            ->count();

        // Award the "Connector" badge if the user has started 5 or more group chats
        $this->updateBadge($user, 'Connector', $groupChatsStarted >= 5);
    }

    // Badge: Challenge-related badge
    public function checkChallengeBadge(User $user)
    {
        $completedChallenges = Challenge::with('users')->get()->filter(function ($challenge) use ($user) {
            return $challenge->isCompletedBy($user);
        })->count();

        $this->updateBadge($user, 'Rookie', $completedChallenges >= 1);
        $this->updateBadge($user, 'Conqueror', $completedChallenges >= 10);
    }

}
