<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Challenge extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = ['name', 'description', 'start_date', 'end_date', 'is_team_based', 'points', 'user_id'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('progress', 'completed')->withTimestamps();
    }

    public function rewards()
    {
        return $this->hasMany(Reward::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_challenge');
    }

    public function getParticipantPoints()
    {
        $participantPoints = [];

        foreach ($this->users as $user) {
            // Calculate total points for this participant
            $totalPoints = $this->tasks()
                ->whereHas('project', function ($query) {
                    $query->whereColumn('tasks.status_id', 'projects.completed_status_id');
                })
                ->whereHas('users', function ($query) use ($user) {
                    $query->where('users.id', $user->id);
                })
                ->sum('points');

            // Store the total points for this participant
            $participantPoints[$user->id] = [
                'user' => $user,
                'total_points' => $totalPoints,
            ];
        }

        return $participantPoints;
    }

    public function isCompletedBy(User $user)
    {
        $participantPoints = $this->getParticipantPoints();
        $userPoints = $participantPoints[$user->id]['total_points'] ?? 0;

        // Check if user points are greater than or equal to challenge points
        return $userPoints >= $this->points;
    }
}
