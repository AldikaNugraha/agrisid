<?php

namespace App\Providers;

use App\Models\FieldWarehouse;
use App\Observers\FieldWarehouseObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Assets\Js;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            LoginResponse::class,
            \App\Http\Responses\LoginResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FieldWarehouse::observe(FieldWarehouseObserver::class);
        Model::unguard();
        FilamentAsset::register([
            Js::make('leaflet-map', resource_path('js/leaflet-map.js')),
            Js::make('leaflet-map-static', resource_path('js/leaflet-map-static.js')),
        ]);
    }
}
