<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\View\Composers\HeaderComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;
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

        // View Composers - untuk optimasi data yang sering dipakai
        View::composer('components.header', HeaderComposer::class);

        View::composer('*', function ($view) {
            $page = Cache::remember('app_pages', 3600, function () {
                return \App\Models\Pages::first();
            });

            $view->with('page', $page);
        });


        // Optimasi Permission Checking untuk Super Admin
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('Super Admin')) {
                return true;
            }
        });

        // Cache permissions untuk sidebar
        View::composer('components.sidebar', function ($view) {
            if (auth()->check()) {
                $userPermissions = Cache::remember(
                    'user_permissions_' . auth()->id(),
                    3600, // Cache 1 jam
                    function () {
                        // Eager load untuk menghindari N+1 query
                        return auth()->user()
                            ->load('roles.permissions', 'permissions')
                            ->getAllPermissions()
                            ->pluck('name')
                            ->toArray();
                    }
                );

                $view->with('userPermissions', $userPermissions);
            }
        });
    }
}
