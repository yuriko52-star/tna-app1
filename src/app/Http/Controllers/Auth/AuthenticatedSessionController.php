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
          $credentials = $request->only('email', 'password');
    $remember = $request->filled('remember');

    // ユーザーを取得
    $user = \App\Models\User::where('email', $credentials['email'])->first();

    if (!$user) {
        return back()->withErrors(['email' => 'メールアドレスが存在しません。']);
    }

    // ロールに応じて guard を分けてログイン
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

       /* if (Auth::guard('admin')->attempt($request->only('email', 'password'), $request->filled('remember'))) {
    return redirect('/admin/attendance/list');
    }
        if (Auth::guard('web')->attempt($request->only('email', 'password'), $request->filled('remember'))) {
    return redirect('/attendance');
}
    */

       /*$credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

       // まず admin ガードで試す
    if (Auth::guard('admin')->attempt($credentials, $remember)) {
        $request->session()->regenerate();
        $admin = Auth::guard('admin')->user();
    if ($admin->isAdmin()) {
            return redirect('/admin/attendance/list');
        }
        // 次に user（web）ガードで試す
    if (Auth::guard('web')->attempt($credentials, $remember)) {
        $request->session()->regenerate();

        $user = Auth::guard('web')->user();
        return redirect('/attendance');
    }

    return back()->withErrors([
        'email' => 'ログイン情報が正しくありません。',
    ]);
    }


         /*if (Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
            
            // ユーザーの役割によってリダイレクトを変更
            if (Auth::user()->isAdmin()) {
                return redirect('/admin/attendance/list'); // 管理者
            }
            return redirect('/attendance'); // ユーザー
        }
        
         return back()->withErrors([
            'email' => 'ログイン情報が正しくありません。',
        ]);
        */

    
        
     public function destroy(Request $request): LogoutResponse
    {
         if (Auth::guard('admin')->check()) {
        Auth::guard('admin')->logout();
    } elseif (Auth::guard('web')->check()) {
        Auth::guard('web')->logout();
    }
        $request->session()->invalidate();
    $request->session()->regenerateToken();


        /*Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
 */
         return app(LogoutResponse::class);
    }
}
 

