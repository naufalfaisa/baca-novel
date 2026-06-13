<?php

namespace App\Providers;

use App\Models\Novel;
use App\Policies\NovelPolicy;
use Illuminate\Console\Application as Artisan;
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
        Gate::policy(Novel::class, NovelPolicy::class);

        Artisan::starting(function ($artisan) {
            $artisan->resolveCommands([
                \App\Console\Commands\FetchRanobeDbData::class,
            ]);
        });
    }
}
