<?php

namespace App\Providers;

use App\Services\PengaturanEmailService;
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
        app(PengaturanEmailService::class)->terapkanKonfigurasiRuntime();
    }
}
