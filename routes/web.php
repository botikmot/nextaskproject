<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TaskController;
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
        $projects = Project::where('user_id', auth()->id()) // Check if the user is the creator
            ->orWhereHas('members', function ($query) {
                $query->where('user_id', auth()->id()); // Check if the user is a member
            })
            ->with('members')
            ->orderBy('created_at', 'desc')
            ->get();
        return Inertia::render($view, [
            'isNewUser' => session('isNewUser', false),
            'userName' => Auth::check() ? Auth::user()->name : null,
            'projects' => $projects,
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
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);
    Route::post('/project-members/{id}', [ProjectController::class, 'addMember'])->name('project.member');
    Route::get('/search-members', [ProjectController::class, 'searchMembers'])->name('search.members');


    
    Route::post('/status/{id}', [StatusController::class, 'store'])->name('status.store');
    Route::get('/status-remove/{id}', [StatusController::class, 'destroy'])->name('status.remove');

    Route::post('/task/{id}', [TaskController::class, 'store'])->name('task.store');
    Route::post('/tasks-update/{id}', [TaskController::class, 'updateTask'])->name('task.update');
});


require __DIR__.'/auth.php';
