<?php

namespace App\Providers;

use App\ProjectsUsers;
use Illuminate\Auth\Access\Response;
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
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('access-project', function ($user, $project_id) {
            return ProjectsUsers::where('project_id', '=', $project_id)
                ->where('user_id', '=', $user->id)
                ->exists();
        });

        Gate::define('update-comment', function ($user, $comment) {
            return $user->id == $comment->user_id;
        });
    }
}
