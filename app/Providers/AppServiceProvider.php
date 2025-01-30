<?php

namespace App\Providers;

use Livewire\Livewire;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Livewire::setScriptRoute(function ($handle) {
            $path = config('app.path') . '/livewire/livewire.js';
            return Route::get($path, $handle)->middleware('web');
        });
        URL::forceRootUrl(config('app.url'));
        // URL::forceScheme(config('app.scheme', 'http'));
    }
}
