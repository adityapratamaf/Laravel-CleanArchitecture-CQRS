<?php

namespace App\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class PresentationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Tambahkan lokasi view dari app/Presentation/Views
        View::addLocation(app_path('Presentation/Views'));
    }
}