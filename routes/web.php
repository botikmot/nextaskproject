<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\GoogleAuthController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;

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
        return Inertia::render($view, [
            'isNewUser' => session('isNewUser', false),
            'userName' => Auth::check() ? Auth::user()->name : null,
            'projects' => $projects,
            'userRole' => $userRole,
        ]);
    })->middleware(['auth', 'verified'])->name(basename($uri));
}

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/project/{id}', [ProjectController::class, 'show'])->name('project.show');
    Route::put('/projects/{id}', [ProjectController::class, 'update']);
    Route::post('/projects/{id}/update-completed-status', [ProjectController::class, 'updateCompletedStatus'])->name('projects.updateCompletedStatus');
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);
    Route::post('/project-members/{id}', [ProjectController::class, 'addMember'])->name('project.member');
    Route::get('/search-members', [ProjectController::class, 'searchMembers'])->name('search.members');

    Route::post('/project/update-user-role', [ProjectController::class, 'addUserRole'])->name('projects.addUserRole');

    
    Route::post('/status/{id}', [StatusController::class, 'store'])->name('status.store');
    Route::get('/status-remove/{id}', [StatusController::class, 'destroy'])->name('status.remove');
    Route::put('/status-update/{id}', [StatusController::class, 'updateStatus'])->name('status.update');

    Route::post('/task/{id}', [TaskController::class, 'store'])->name('task.store');
    Route::post('/tasks-update/{id}', [TaskController::class, 'updateTask'])->name('task.update');
    Route::delete('/task-remove/{id}', [TaskController::class, 'destroy'])->name('task.remove');
    Route::post('/assign-member/{id}', [TaskController::class, 'assignMembers'])->name('task.assign');
    Route::post('/remove-member/{id}', [TaskController::class, 'removeMembers'])->name('task.memberRemove');
    Route::post('/task-comment/{id}', [TaskController::class, 'taskComment'])->name('task.comment');
    Route::delete('/task-comment/{id}', [TaskController::class, 'removeComment'])->name('task.commentRemove');
    Route::put('/task-comment/{id}', [TaskController::class, 'updateComment'])->name('task.commentUpdate');

    Route::post('/task/{taskId}/subtask', [TaskController::class, 'createSubtask'])->name('task.createSubtask');
    Route::put('/task/{subtaskId}/subtask', [TaskController::class, 'updateSubtask'])->name('task.updateSubtask');

    Route::get('/tasks/{taskId}/can-start', [TaskController::class, 'canStartTask'])->name('task.canStartTask');
    Route::post('/tasks/{taskId}/set-dependency', [TaskController::class, 'setDependency'])->name('task.setDependency');

});


Route::get('auth/google', [GoogleAuthController::class, 'redirect'])->name('google-auth');
Route::get('auth/google/callback', [GoogleAuthController::class, 'callbackGoogle']);

require __DIR__.'/auth.php';
