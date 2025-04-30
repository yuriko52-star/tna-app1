<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomLoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
// admin guard でログイン中か確認   
        $admin = Auth::guard('admin')->user();

        if ($admin && $admin->isAdmin()) {
            return redirect('/admin/attendance/list'); // 管理者のダッシュボード
        }

        // 一般ユーザー（web guard）
        $user = Auth::guard('web')->user();

        if($user && ! $user->hasVerifiedEmail()) {
        // メール未認証なら、認証誘導画面へリダイレクト
        return redirect()->route('verification.notice');
        }

        if ($user) {
            return redirect('/attendance'); // 一般ユーザーのダッシュボード
        }

        // それ以外はログインページにリダイレクト
        return redirect('/login');
    }
        
        /*$user = Auth::user();

        if ($user->isAdmin()) {
            return redirect('/admin/attendance/list'); // 管理者のリダイレクト
        }

        return redirect('/attendance'); // 一般ユーザーのリダイレクト
        */
        
    
}
