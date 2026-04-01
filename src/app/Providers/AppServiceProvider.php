<?php

namespace App\Providers;

use App\Models\Task;
use App\Models\Project;
use App\Models\Comment;
use App\Policies\TaskPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\CommentPolicy;
use Illuminate\Support\Facades\Gate; 
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);
    }
}
