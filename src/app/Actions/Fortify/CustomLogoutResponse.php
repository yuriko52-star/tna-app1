<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class CustomLogoutResponse implements LogoutResponseContract
{
   
    public function toResponse($request)
    {
        $previousUrl = url()->previous();
        if (Str::startsWith($previousUrl, url('/admin'))) {
        return redirect('/admin/login');
        }
        return redirect('/login');



       // ログアウト後のリダイレクト先を決定
       /* $redirectTo = $request->is('admin/*') ? '/admin/login' : '/login';

        return redirect($redirectTo);
        */
        /*if (Auth::guard('admin')->check()) {
        return redirect('/admin/login');
    }

    return redirect('/login');
    */
    }
    
}