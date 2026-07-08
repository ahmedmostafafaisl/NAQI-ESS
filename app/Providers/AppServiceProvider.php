<?php

namespace App\Providers;

use App\Services\Dynamics365Service;
use App\Services\FcmService;
use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Contract\Messaging;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Dynamics365Service::class, fn() => new Dynamics365Service());

        $this->app->singleton(FcmService::class, fn($app) => new FcmService($app->make(Messaging::class)));
    }

    public function boot(): void
    {
        //
    }
}
