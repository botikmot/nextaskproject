<?php

namespace App\Services;

use App\Models\Level;
use App\Models\User;

class LevelService
{
    public function checkAndUpdateLevel(User $user)
    {
        $currentPoints = $user->totalPoints(); // Assuming the User model has a points column

        $newLevel = Level::where('points_required', '<=', $currentPoints)
            ->orderBy('points_required', 'desc')
            ->first();

        if ($newLevel && $user->level_id !== $newLevel->id) {
            $user->level_id = $newLevel->id;
            $user->save();
        }
    }
}
