<?php

namespace App\Providers;

use App\Auth\SupabaseUserProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Register custom Supabase user provider
        Auth::provider('supabase', function ($app, array $config) {
            return new SupabaseUserProvider();
        });
    }
}
