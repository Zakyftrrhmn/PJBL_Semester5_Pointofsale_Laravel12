<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class HeaderComposer
{
    /**
     * Bind data ke header view dengan caching untuk optimasi performa
     */
    public function compose(View $view)
    {
        if (Auth::check()) {
            // Cache user data beserta relasi roles (menghindari multiple queries)
            $user = Cache::remember('header_user_' . Auth::id(), 3600, function () {
                return Auth::user()->load('roles');
            });

            // Cache role names (menghindari query getRoleNames() di setiap render)
            $roleNames = Cache::remember('header_roles_' . Auth::id(), 3600, function () use ($user) {
                return $user->getRoleNames()->implode(', ');
            });

            // Pass data ke view
            $view->with([
                'currentUser' => $user,
                'userRoles' => $roleNames
            ]);
        }
    }
}
