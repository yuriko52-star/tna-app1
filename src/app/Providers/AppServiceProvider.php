<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (Request::is('admin/*')) {
        Config::set('session.cookie', 'admin_session'); // ✅ 管理者用セッション
    } else {
        Config::set('session.cookie', 'user_session'); // ✅ 一般ユーザー用セッション
    }
    }
}
