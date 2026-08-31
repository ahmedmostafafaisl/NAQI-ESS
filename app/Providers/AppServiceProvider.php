<?php

namespace App\Providers;

use App\Services\Dynamics365Service;
use App\Services\FcmService;
use App\Services\FirestoreService;
use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Contract\Messaging;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Dynamics365Service::class, fn() => new Dynamics365Service());

        $this->app->singleton(FcmService::class, fn($app) => new FcmService($app->make(Messaging::class)));

        // No constructor dependencies — it's a plain REST client, no
        // google/cloud-firestore or Kreait Firestore contract needed.
        $this->app->singleton(FirestoreService::class, fn() => new FirestoreService());
    }

    public function boot(): void
    {
        //
    }
}
