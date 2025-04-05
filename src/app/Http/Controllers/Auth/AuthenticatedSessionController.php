<?php

namespace App\Http\Controllers\Auth;



use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController as FortifyAuthenticatedSessionController;
use Laravel\Fortify\Contracts\LogoutResponse; 
 use Laravel\Fortify\Http\Requests\LoginRequest;

class AuthenticatedSessionController extends FortifyAuthenticatedSessionController
{
  public function store(LoginRequest $request)
    {
       

       
         if (Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
            
            // ユーザーの役割によってリダイレクトを変更
            if (Auth::user()->isAdmin()) {
                return redirect('/admin/attendance/list'); // 管理者
            }
            return redirect('/attendance'); // ユーザー
        }
        
         return back()->withErrors([
            'email' => 'ログイン情報が正しくありません。',
        ]);

    }
        
     public function destroy(Request $request): LogoutResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
 
         return app(LogoutResponse::class);
    }
}
 

