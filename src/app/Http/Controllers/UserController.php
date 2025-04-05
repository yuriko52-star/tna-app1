<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\BreakTime;

class UserController extends Controller
{
    
    public function index() {
        $user = Auth::user();
        
        $today = now()->isoFormat('YYYY年M月D日(ddd)');
        $currentTime = now()->format('H:i');


        $attendance = Attendance::where('user_id',$user->id)->whereDate('date',now()->toDateString())->first();
        
        $status = $this->getCurrentStatus($user);
       
        return view('attendance.record',compact('status','today','currentTime'));
    }
    private function getCurrentStatus($user)
    {
        // 直近の勤怠データを取得（データベースのカラムに応じて変更）
        $attendance = Attendance::where('user_id',$user->id)->WhereDate('date',now()->toDateString())->latest()->first();
        if(!$attendance) {
            return '勤務外';
        }
        // 勤怠ステータスの判定

        
        if($attendance->clock_out) {
            return '退勤済';
        } 
        
        $breakTime = BreakTime::where('attendance_id',$attendance->id)->latest()->first();
        if($breakTime && $breakTime->clock_in && !$breakTime->clock_out) {
            return '休憩中';
        } 
         if ($attendance->clock_in) {
             return '出勤中';
         }
        
        return '勤務外';

    }
}
