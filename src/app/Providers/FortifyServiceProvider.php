<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\CustomLoginResponse;
use Laravel\Fortify\Contracts\LoginResponse;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Actions\Fortify\CustomLogoutResponse;
use Laravel\Fortify\Contracts\LogoutResponse;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Hash;
 use Illuminate\Support\Facades\Auth;
use App\Models\User;
 use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
 use App\Http\Requests\LoginRequest;
 use Illuminate\Support\Facades\View;
 use Laravel\Fortify\Contracts\VerifyEmailViewResponse;
 use App\Http\Requests\RegistrationRequest;
 use Laravel\Fortify\Contracts\RegisterResponse;
use App\Actions\Fortify\CustomRegisterResponse; 


class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

         $this->app->singleton(LoginResponse::class, CustomLoginResponse::class);
        $this->app->singleton(LogoutResponse::class, CustomLogoutResponse::class);    
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        
        Fortify::registerView(function () {
         return view('auth.register');
        });
        Fortify::verifyEmailView(function() {
            return view('auth.verify-email');
        });  
        $this->app->bind(
            \Laravel\Fortify\Contracts\RegisterResponse::class,
            \App\Actions\Fortify\CustomRegisterResponse::class
        );

        Fortify::loginView(function () { 
                return view('auth.login'); 
        });
        
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            if($user && Hash::check($request->password, $user->password)) {
                
                if ($request->is('admin/login') && $user->role === 'admin') {
                    Auth::guard('admin')->login($user);
                    return $user;
                } elseif ($request->is('login') && $user->role === 'user') {
                    Auth::guard('web')->login($user);
                    return $user;
                }
            }
            
                return null;
        });

        RateLimiter::for('login', function (Request $request) {
             $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });   
        
    }
}
