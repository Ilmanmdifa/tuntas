<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * AppServiceProvider — main app provider (clean for Todo portfolio)
 * Book/Greeting demo archived to trial-and-error/book-demo-arsip/ (Day 31 cleanup)
 * Why empty? Todo uses Eloquent directly, no Interface bind needed now
 */
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
        //
    }
}
