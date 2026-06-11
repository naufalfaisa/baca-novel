<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Application as Artisan;

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
        Artisan::starting(function ($artisan) {
            $artisan->resolveCommands([
                \App\Console\Commands\FetchRanobeDbData::class,
            ]);
        });
    }
}
