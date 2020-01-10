<?php

namespace App\Providers;

use App\Project;
use App\User;
use App\Comment;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        \App\Project::class => \App\Policies\ProjectPolicy::class,
        \App\Status::class => \App\Policies\StatusPolicy::class,
        \App\Task::class => \App\Policies\TaskPolicy::class,
        \App\Comment::class => \App\Policies\CommentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('access-project', function (User $user, $project_id) {
            $project = Project::findOrFail($project_id);
            return $project->users()->where(['user_id' => $user->id])->exists();
        });

    }
}
