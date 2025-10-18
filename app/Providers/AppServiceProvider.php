<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\View\Composers\NotificationComposer;
use Illuminate\Support\Facades\View;
use App\Models\Pages;


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
        Paginator::useTailwind();
        View::composer('components.header', NotificationComposer::class);

        $page = Pages::first();
        View::share('page', $page);
    }
}
