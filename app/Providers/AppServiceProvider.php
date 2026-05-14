<?php

namespace App\Providers;

use App\Models\CandidateCv;
use App\Observers\CandidateCvObserver;
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
        CandidateCv::observe(CandidateCvObserver::class);
    }
}
