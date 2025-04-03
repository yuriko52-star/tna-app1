<?php

namespace App\Http\Controllers\Auth;


// use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController as FortifyAuthenticatedSessionController;
use Laravel\Fortify\Contracts\LogoutResponse; // 追加
// use Laravel\Fortify\Http\Requests\LoginRequest;

class AuthenticatedSessionController extends FortifyAuthenticatedSessionController
{
  public function store(Request $request)
    {
        //   Log::info('ログイン処理開始'); // ここでログイン処理が呼ばれたことを確認
        // セッションを開始
    // if (!session()->isStarted()) {
        // Log::info('セッションを開始します');
        // session()->start();
    // }

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
         if (Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
            //  Log::info('ログイン成功：' . Auth::user()->email);
            // $request->session()->regenerate();

            // ユーザーの役割によってリダイレクトを変更
            if (Auth::user()->isAdmin()) {
                return redirect('/admin/attendance/list'); // 管理者
            }
            return redirect('/attendance'); // ユーザー
        }
        // Log::error('ログイン失敗: ' . $request->email);
         return back()->withErrors([
            'email' => 'ログイン情報が正しくありません。',
        ]);

    }
        
     public function destroy(Request $request): LogoutResponse
    {
        Auth::logout();
    // Log::info('ログアウト処理開始: ' . Auth::user()->email); // デバッグ用

    // Auth::guard('web')->logout(); // ユーザーをログアウト

        $request->session()->invalidate();
        $request->session()->regenerateToken();
 // ログアウトしたユーザーが管理者なら `/admin/login` へ、それ以外は `/login` へ
        // $redirectTo = $request->is('admin/*') ? '/admin/login' : '/login';
         return app(LogoutResponse::class);
        // ->toResponse($request)->setTargetUrl($redirectTo);
    
    
    // 管理者かどうかでリダイレクト先を変える
         /*if ($request->is('admin/*')) {
            return redirect('/admin/login'); // 管理者なら管理者ログインページへ
        }
        return redirect('/login');*/
         // 一般ユーザーなら通常のログインページへ
    }
}
 

