<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_image',
        'last_login',
        'google_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid(); // Automatically generate UUID on creation
            }
        });
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function projectMemberships()
    {
        return $this->belongsToMany(Project::class, 'project_user_role')->withTimestamps();
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_user');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'project_user_role')
                    ->withPivot('project_id')
                    ->withTimestamps();
    }

    public function projectsWithRoles()
    {
        return $this->belongsToMany(Project::class, 'project_user_role')
                    ->withPivot('role_id')
                    ->withTimestamps();
    }

    public function mainRoles()
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    // Method to check if a user has a specific role
    public function hasRole($roleName)
    {
        return $this->mainRoles()->where('name', $roleName)->exists();
    }

    // Method to assign a role to the user
    public function assignRole($role)
    {
        $this->mainRoles()->attach($role);
    }

    public function createdEvents()
    {
        return $this->hasMany(Event::class, 'creator_id');
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_user');
    }

    public function getAllEvents()
    {
        $events = Event::with(['creator', 'participants'])
            ->where('creator_id', $this->id) // Check if the user is the creator
            ->orWhereHas('participants', function ($query) {
                $query->where('user_id', $this->id); // Check if the user is a participant
            })
            ->get();

        return $events->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start,
                'end' => $event->end,
                'allDay' => $event->all_day,
                'location' => $event->location,
                'participants' => $event->participants,
                'backgroundColor' => $event->background_color,
                'extendedProps' => [
                    'description' => $event->description,
                    'creator' => $event->creator->name,
                    'creator_id' => $event->creator->id,
                ],
            ];
        });
    }


    public function friends()
    {
        return $this->belongsToMany(User::class, 'friendships', 'user_id', 'friend_id')
                ->select('users.id', 'users.name', 'users.profile_image', 'friendships.friend_id', 'users.last_login');
    }

    public function friendsWithMutualProjects()
    {
        return $this->friends()->get()->map(function ($friend) {
            // Calculate mutual projects count
            $mutualProjectsCount = $this->projectMemberships()
                ->pluck('id')
                ->intersect($friend->projectMemberships()->pluck('id'))
                ->count();

            // Append the mutual_projects count to the friend model
            $friend->mutual_projects = $mutualProjectsCount;

            return $friend;
        });
    }

    public function sentFriendRequests()
    {
        return $this->hasMany(FriendRequest::class, 'sender_id');
    }

    public function receivedFriendRequests()
    {
        return $this->hasMany(FriendRequest::class, 'receiver_id')->with('sender')->pending();
    }

    public function suggestedFriends($max = 3)
    {
        $friendIds = $this->friends()->pluck('friend_id')->toArray();
        $friendIds[] = $this->id; // Include the current user's ID to exclude themselves

        // Get IDs of users with pending friend requests
        $pendingRequestsSenderIds = $this->sentFriendRequests()->where('status', 'pending')->pluck('receiver_id')->toArray();
        $pendingRequestsReceiverIds = $this->receivedFriendRequests()->where('status', 'pending')->pluck('sender_id')->toArray();

        $excludedIds = array_merge($friendIds, $pendingRequestsSenderIds, $pendingRequestsReceiverIds);

        // Fetch project members where the auth user is a participant
        $projectMemberIds = Project::whereHas('users', function ($query) {
                $query->where('user_id', $this->id);
            })->with('users')
            ->get()
            ->flatMap(function ($project) {
                return $project->users->pluck('id');
            })
            ->unique()
            ->reject(fn($id) => in_array($id, $excludedIds))
            ->values();

        // Step 1: Prioritize users in projects
        $suggestedFriends = User::whereIn('id', $projectMemberIds)
            ->inRandomOrder()
            ->take($max)
            ->get()
            ->map(function ($suggestedUser) {
                // Calculate the number of mutual projects between the auth user and suggested user
                $mutualProjectsCount = $this->projectMemberships->pluck('id')
                    ->intersect($suggestedUser->projectMemberships->pluck('id'))
                    ->count();

                // Add mutual projects count to each user
                $suggestedUser->mutual_projects = $mutualProjectsCount;
                return $suggestedUser;
            });

        // Step 2: If not enough friends, fill remaining with other users
        if ($suggestedFriends->count() < $max) {
            $remainingCount = $max - $suggestedFriends->count();

            $additionalFriends = User::whereNotIn('id', array_merge($excludedIds, $projectMemberIds->toArray()))
                ->inRandomOrder()
                ->take($remainingCount)
                ->get()
                ->map(function ($suggestedUser) {
                    // Calculate the number of mutual projects for the additional suggested users
                    $mutualProjectsCount = $this->projectMemberships->pluck('id')
                        ->intersect($suggestedUser->projectMemberships->pluck('id'))
                        ->count();

                    // Add mutual projects count to each user
                    $suggestedUser->mutual_projects = $mutualProjectsCount;
                    return $suggestedUser;
                });

            $suggestedFriends = $suggestedFriends->merge($additionalFriends);
        }

        return $suggestedFriends;
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class)->withPivot('is_admin');
    }

    // Get all private conversations for the authenticated user
    public function privateConversations()
    {
        return $this->conversations()->where('type', 'private');
    }

    // Get all group conversations for the authenticated user
    public function groupConversations()
    {
        return $this->conversations()->where('type', 'group');
    }

    // Get private conversations with a specific user
    public function privateConversationsWith($userId)
    {
        return $this->conversations()
            ->where('type', 'private')
            ->whereHas('users', function ($query) use ($userId) {
                $query->where('users.id', $userId);
            });
    }

    public function notifications()
    {
        //return $this->hasMany(Notification::class, 'notifiable_id'); 
        return $this->morphMany(Notification::class, 'notifiable');
    }

    public function unreadNotifications()
    {
        return $this->notifications()->whereNull('read_at')->orderBy('created_at', 'desc')->with('user');
    }

    public function readNotifications()
    {
        return $this->notifications()->whereNotNull('read_at');
    }

    public function challenges()
    {
        return $this->belongsToMany(Challenge::class)->withPivot('progress', 'completed')->withTimestamps();
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function totalPoints()
    {
        return $this->tasks()
            ->whereHas('project', function ($query) {
                $query->whereColumn('tasks.status_id', 'projects.completed_status_id');
            })
            ->sum('points');
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badge');
    }

    public function totalTaskCompleted()
    {
        return $this->tasks()
            ->whereHas('project', function ($query) {
                $query->whereColumn('tasks.status_id', 'projects.completed_status_id');
            })->count();
    }

    public function tasksAheadOfDeadline()
    {
        return $this->tasks()
            ->whereHas('project', function ($query) {
                $query->whereColumn('tasks.status_id', 'projects.completed_status_id');
            })
            ->whereColumn('tasks.updated_at', '<', 'tasks.due_date') // Compare updated_at to deadline
            ->count();
    }


    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
