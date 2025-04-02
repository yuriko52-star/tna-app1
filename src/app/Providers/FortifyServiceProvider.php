<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
//  use App\Actions\Fortify\CustomLogoutResponse;

// use Illuminate\Support\Facades\Session;
// use App\Actions\Fortify\ResetUserPassword;
// use App\Actions\Fortify\UpdateUserPassword;
// use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
// use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Hash;
 use Illuminate\Support\Facades\Auth;
use App\Models\User;
// use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;まだ作成していない。作成したら、適用
// use App\Http\Requests\LoginRequest;同上
// use Illuminate\Support\Facades\View;いるか不明メール認証で？
// use Laravel\Fortify\Contracts\VerifyEmailViewResponse;メール認証の時にインポート
// use App\Http\Requests\RegistrationRequest;バリデーション作成したら適用


class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //    $this->app->singleton(LogoutResponse::class, CustomLogoutResponse::class);    
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        // Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        // Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        // Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // RateLimiter::for('login', function (Request $request) {
            // $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            // return Limit::perMinute(5)->by($throttleKey);
        // });

        // RateLimiter::for('two-factor', function (Request $request) {
            // return Limit::perMinute(5)->by($request->session()->get('login.id'));
        // });
        Fortify::registerView(function () {
         return view('auth.register');
        });
        // メール認証時に追加する予定
         Fortify::redirects(
             'register','/attendance'
          );
            


        
        
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            if($user && Hash::check($request->password, $user->password)) {
                return $user;
            }
            return null;
        });
            Fortify::loginView(function () { 
                return view('auth.login'); 
        });

        // Fortifyのログイン処理をカスタムコントローラーに変更
        Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware(['guest']);

         // ログイン後のリダイレクト先をカスタマイズ
            /*Fortify::authenticated(function (Request $request, $user) {
                if($user->isAdmin()) {
                    return redirect('/attendance/list');
                }
                return redirect('/attendance');
        });
*/

       

         RateLimiter::for('login', function (Request $request) {
             $email = (string) $request->email;

           return Limit::perMinute(10)->by($email . $request->ip());
        });   
        // $this->app->bind(FortifyLoginRequest::class, LoginRequest::class);バリデーションで適用
    }
}
