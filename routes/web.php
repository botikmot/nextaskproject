<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\FriendshipController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\TopicController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use App\Models\Event;
use App\Models\Post;
//use App\Models\Challenge;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Define a list of routes that require authentication and rendering a view
$routes = [
    '/dashboard' => 'Dashboard',
    '/my-tasks' => 'Tasks/MyTasks',
    '/projects' => 'Projects/Projects',
    '/social' => 'Social/SocialFeed',
    '/messages' => 'Messages/Messages',
    '/calendar' => 'Calendar/Calendar',
    '/challenge' => 'Challenge/Dashboard',
];

foreach ($routes as $uri => $view) {
    Route::get($uri, function () use ($view) {
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
        $events = Event::with(['creator', 'participants'])
            ->where('creator_id', $user->id)  // Check if the user is the creator
            ->orWhereHas('participants', function($query) use ($user) {
                $query->where('user_id', $user->id); // Check if the user is a participant
            })
            ->get();
        
        $formattedEvents = $events->map(function ($event) {
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

        return Inertia::render($view, [
            'isNewUser' => session('isNewUser', false),
            'userName' => Auth::check() ? Auth::user()->name : null,
            'projects' => $projects,
            'userRole' => $userRole,
            'events' => $formattedEvents,
        ]);
    })->middleware(['auth', 'verified'])->name(basename($uri));
}

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware('auth')->group(function () {

    // Projects Tasks
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/project/{id}', [ProjectController::class, 'show'])->name('project.show');
    Route::put('/projects/{id}', [ProjectController::class, 'update']);
    Route::post('/projects/{id}/update-completed-status', [ProjectController::class, 'updateCompletedStatus'])->name('projects.updateCompletedStatus');
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);
    Route::post('/project-members/{id}', [ProjectController::class, 'addMember'])->name('project.member');
    Route::get('/search-members', [ProjectController::class, 'searchMembers'])->name('search.members');

    Route::post('/project/update-user-role', [ProjectController::class, 'addUserRole'])->name('projects.addUserRole');
    Route::get('/tasks-project', [TaskController::class, 'getTasksProject'])->name('task.getTasksProject');

    Route::get('/my-tasks', [TaskController::class, 'getTasks'])->name('my-tasks');
    
    Route::post('/status/{id}', [StatusController::class, 'store'])->name('status.store');
    Route::get('/status-remove/{id}', [StatusController::class, 'destroy'])->name('status.remove');
    Route::put('/status-update/{id}', [StatusController::class, 'updateStatus'])->name('status.update');

    Route::post('/task/{id}', [TaskController::class, 'store'])->name('task.store');
    Route::put('/tasks-update/{id}', [TaskController::class, 'updateTask'])->name('task.updateTask');
    Route::post('/tasks-update-status/{id}', [TaskController::class, 'updateTaskStatus'])->name('task.updateTaskStatus');
    Route::delete('/task-remove/{id}', [TaskController::class, 'destroy'])->name('task.remove');
    Route::post('/assign-member/{id}', [TaskController::class, 'assignMembers'])->name('task.assign');
    Route::post('/remove-member/{id}', [TaskController::class, 'removeMembers'])->name('task.memberRemove');
    Route::post('/task-comment/{id}', [TaskController::class, 'taskComment'])->name('task.comment');
    Route::delete('/task-comment/{id}', [TaskController::class, 'removeComment'])->name('task.commentRemove');
    Route::put('/task-comment/{id}', [TaskController::class, 'updateComment'])->name('task.commentUpdate');
    Route::post('/tasks/label/{projectId}', [TaskController::class, 'storeLabel'])->name('task.storeLabel');
    Route::put('/tasks/label/{id}', [TaskController::class, 'updateLabel'])->name('task.updateLabel');
    Route::delete('/tasks/label/{id}', [TaskController::class, 'removeLabel'])->name('task.removeLabel');
    Route::post('/task/{taskId}/subtask', [TaskController::class, 'createSubtask'])->name('task.createSubtask');
    Route::put('/task/{subtaskId}/subtask', [TaskController::class, 'updateSubtask'])->name('task.updateSubtask');
    Route::delete('/task/{subtaskId}/subtask', [TaskController::class, 'removeSubtask'])->name('task.removeSubtask');

    Route::get('/tasks/{taskId}/can-start', [TaskController::class, 'canStartTask'])->name('task.canStartTask');
    Route::post('/tasks/{taskId}/set-dependency', [TaskController::class, 'setDependency'])->name('task.setDependency');
    Route::post('/tasks/{taskId}/remove-dependency', [TaskController::class, 'removeDependency'])->name('task.removeDependency');
    Route::put('/task-description/{id}', [TaskController::class, 'updateTaskDescription'])->name('task.taskDescription');
    Route::get('/tasks/tasks-completion-rate', [TaskController::class, 'getTaskCompletionRate'])->name('task.getTaskCompletionRate');
    Route::post('/tasks/{task}/start-timer', [TaskController::class, 'startTimer'])->name('tasks.startTimer');
    Route::post('/tasks/{task}/stop-timer', [TaskController::class, 'stopTimer'])->name('tasks.stopTimer');
    Route::get('/tasks/{task}/time', [TaskController::class, 'getTaskTime']);
    Route::get('/tasks/active', [TaskController::class, 'getActiveTasks']);
    Route::post('/tasks/{task}/toggle-time-tracking', [TaskController::class, 'toggleTimeTracking']);



    // Calendar Events
    Route::post('/events', [EventController::class, 'store'])->name('event.store');
    Route::post('/events/{id}', [EventController::class, 'update'])->name('event.update');
    Route::get('/events', [EventController::class, 'index'])->name('event.index');

    // Social
    Route::post('/post', [PostController::class, 'store'])->name('post.store');
    Route::post('/friend-requests', [FriendshipController::class, 'send']);
    Route::post('/friend-requests/{id}/accept', [FriendshipController::class, 'accept']);
    Route::post('/friend-requests/{id}/reject', [FriendshipController::class, 'reject']);
    Route::get('/social', [PostController::class, 'index'])->name('social');
    Route::get('/users/search', [PostController::class, 'searchUsers']);
    Route::delete('/post/{id}', [PostController::class, 'destroy'])->name('post.delete');
    Route::put('/post/{id}', [PostController::class, 'update'])->name('post.update');
    Route::post('/post/comment/{id}', [PostController::class, 'commentPost'])->name('post.commentPost');
    Route::delete('/post/comment/{id}', [PostController::class, 'postCommentDelete'])->name('post.postCommentDelete');
    Route::put('/post/comment/{id}', [PostController::class, 'postCommentUpdate'])->name('post.postCommentUpdate');
    Route::post('/post/{id}/like', [PostController::class, 'likePost'])->name('post.likePost');
    Route::get('/social/highlights', [PostController::class, 'getSocialHighlights']);

    // Trending Topics
    Route::get('/trending', [TopicController::class, 'index'])->name('trending.index');


    // Messages 
    Route::post('/conversations/private', [MessageController::class, 'createPrivateConversation']);
    Route::post('/conversations/group', [MessageController::class, 'createGroupConversation']);
    Route::post('/conversations/{conversationId}/messages', [MessageController::class, 'sendMessage']);
    Route::get('/conversations/{conversationId}/messages', [MessageController::class, 'getMessages']);
    Route::get('/conversations/recent-chats', [MessageController::class, 'getRecentChats']);
    Route::post('/messages/mark-as-read', [MessageController::class, 'markAsRead']);

    //Notifications
    Route::post('/notifications/chat/read', [NotificationController::class, 'markNotificationChatAsRead']);
    Route::post('/notifications/read/{id}', [NotificationController::class, 'markNotificationAsRead']);

    // Challenges
    Route::post('/challenges', [ChallengeController::class, 'create'])->name('challenge.create');
    Route::put('/challenges/{challengeId}', [ChallengeController::class, 'update'])->name('challenge.update');
    Route::delete('/challenges/{challengeId}', [ChallengeController::class, 'destroy']);
    Route::get('/challenges', [ChallengeController::class, 'index'])->name('challenge');
    Route::post('/challenges/{challenge}/join', [ChallengeController::class, 'join']);
    Route::post('/challenges/{challengeId}/rewards', [ChallengeController::class, 'addReward']);
    Route::get('/challenges/{challenge}/progress', [ChallengeController::class, 'viewProgress']);
    Route::delete('/challenges/{challengeId}/leave', [ChallengeController::class, 'leaveChallenge']);

});


Route::get('auth/google', [GoogleAuthController::class, 'redirect'])->name('google-auth');
Route::get('auth/google/callback', [GoogleAuthController::class, 'callbackGoogle']);

require __DIR__.'/auth.php';
