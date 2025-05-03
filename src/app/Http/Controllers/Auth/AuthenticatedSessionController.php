<?php

namespace App\Http\Controllers\Auth;



use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController as FortifyAuthenticatedSessionController;
use Laravel\Fortify\Contracts\LogoutResponse; 
use Laravel\Fortify\Http\Requests\LoginRequest;
use App\Models\User;

class AuthenticatedSessionController extends FortifyAuthenticatedSessionController
{
  public function store(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return back()->withErrors(['email' => 'メールアドレスが存在しません。']);
        }

    
        if ($user->isAdmin()) {
            if (Auth::guard('admin')->attempt($credentials, $remember)) {
                return redirect('/admin/attendance/list');
                }
            } else {
        if (Auth::guard('web')->attempt($credentials, $remember)) {
                return redirect('/attendance');
            }
        }

            return back()->withErrors([
                'email' => 'ログイン情報が正しくありません。',
        ]);
    }

       
    public function destroy(Request $request): LogoutResponse
    {
         if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        } elseif (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return app(LogoutResponse::class);
    }
}
 

