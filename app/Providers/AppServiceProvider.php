<?php

namespace App\Providers;

use App\Support\BmsPermissions;
use App\View\Composers\BmsLayoutComposer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer(['layouts.bms', 'bms.*'], BmsLayoutComposer::class);

        Blade::if('canBms', function (string $resource, string $action) {
            return BmsPermissions::allowed(Auth::user()?->role, $resource, $action);
        });
    }
}
