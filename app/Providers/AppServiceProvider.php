<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use App\Models\Task;
use App\Observers\TaskObserver;
use App\Services\LevelService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the LevelService as a singleton
        $this->app->singleton(LevelService::class, function ($app) {
            return new LevelService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Inertia::share('flash', function () {
            return [
                'success' => Session::get('success'),
                'error' => Session::get('error'),
                'message' => Session::get('message'),
                'friends' => Session::get('friends'),
                'tasks' => Session::get('tasks'),
            ];
        });

        Task::observe(TaskObserver::class);

    }
}
