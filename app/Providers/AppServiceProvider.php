<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Paginator::useBootstrap();
        Schema::defaultStringLength(191);

        View::composer('layouts.app', function ($view) {
            $unread = 0;
            $latest = collect();

            if (auth()->check()) {
                try {
                    $query = auth()->user()->notifications()->latest();
                    $unread = (clone $query)->whereNull('lu_at')->count();
                    $latest = $query->take(6)->get();
                } catch (\Throwable $e) {
                    $unread = 0;
                    $latest = collect();
                }
            }

            $view->with([
                'unreadNotifications' => $unread,
                'latestNotifications' => $latest,
                'appSettings' => Setting::map(),
            ]);
        });
    }
}
