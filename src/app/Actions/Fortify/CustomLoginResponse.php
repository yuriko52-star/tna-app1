<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomLoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {

        
        $user = Auth::user();

        if ($user->isAdmin()) {
            return redirect('/admin/attendance/list'); // 管理者のリダイレクト
        }

        return redirect('/attendance'); // 一般ユーザーのリダイレクト
        
    }
}
