<?php

namespace App\Providers;

use App\Models\FaceProfile;
use App\Observers\FaceProfileObserver;
use Illuminate\Support\Facades\URL;
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
        if (config('app.force_https')) {
            URL::forceScheme('https');
        }

        // Daftarkan observer untuk auto-sync face profile ke Python
        FaceProfile::observe(FaceProfileObserver::class);
    }
}
