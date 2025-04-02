<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LogoutResponse;
use Illuminate\Http\Request;

class CustomLogoutResponse implements LogoutResponse
{
    /**
     * Handle logout response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toResponse($request)
    {
        // Auth::logout();
         /*if ($request->is('admin/*')) {

            return redirect('/admin/login');
        }
        return redirect('/login');
        */
      return redirect($request->is('admin/*') ? '/admin/login' : '/login');
    }
}