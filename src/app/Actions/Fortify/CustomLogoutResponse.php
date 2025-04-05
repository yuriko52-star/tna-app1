<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Http\Request;

class CustomLogoutResponse implements LogoutResponseContract
{
   
    public function toResponse($request)
    {
       // ログアウト後のリダイレクト先を決定
        $redirectTo = $request->is('admin/*') ? '/admin/login' : '/login';

        return redirect($redirectTo);
        
    }
}