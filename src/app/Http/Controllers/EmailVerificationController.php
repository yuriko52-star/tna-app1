<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class EmailVerificationController extends Controller
{
   public function show()
    {
        return view('auth.verify-email');
    } 

    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();

        $user = $request->user();
       
        return redirect()->route('user.attendance')->with('status', 'Email verified!');
    }

    public function resend(Request $request)
    {
        
        return back()->with('message', 'Verification email resent!');
    }

}
