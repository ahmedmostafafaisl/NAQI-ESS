<?php

namespace App\Providers;

use App\Services\Dynamics365Service;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Dynamics365Service::class, fn () => new Dynamics365Service());
    }

    public function boot(): void
    {
        //
    }
}
