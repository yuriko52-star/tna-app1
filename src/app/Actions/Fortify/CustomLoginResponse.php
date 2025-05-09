<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomLoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $admin = Auth::guard('admin')->user();

        if ($admin && $admin->isAdmin()) {
            return redirect('/admin/attendance/list'); 
        }

        $user = Auth::guard('web')->user();

         if($user && ! $user->hasVerifiedEmail()) {
        
            return redirect()->route('verification.notice');
        }
            if ($user) {
            return redirect('/attendance'); 
        }
           return redirect('/login');  
        
    }
        
}   