<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceEdit;
use App\Models\BreakTimeEdit;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    
    public function clockIn()
    {
        
        $user = Auth::user();

        $today = now()->toDateString();
        $attendance = Attendance::where('user_id',$user->id)->where('date',$today)->first();
        if(!$attendance) {
            $attendance = new Attendance();
            $attendance->user_id = $user->id;
            $attendance->date = $today;
        }
        
        //  if(!is_null($attendance->clock_in)) {
            //  return redirect()->back()->with('message','すでに出勤済みです。');
        //  }
        
          $attendance->clock_in = now();
          $attendance->save();
         
            
        return redirect()->route('user.attendance');
    }
    public function clockOut()
    {
        $user = Auth::user();
        $attendance = Attendance::where('user_id', $user->id)->where('date', now()->toDateString())->first();
        if(!$attendance || $attendance->clock_out) {
            return redirect()->back()->with('msssage','まだ出勤していません。');
        }
        $attendance->update([
                'clock_out' => now()
            ]);
        
        return redirect()->route('user.attendance');
    }
    public function breakStart()
    {
        $user = Auth::user();
        $attendance = Attendance::where('user_id', $user->id)->where('date', now()->toDateString())->first();
        $lastBreak = $attendance->breakTimes()->latest()->first();
        
        if($lastBreak && !$lastBreak->clock_out) {
            return redirect()->back()->with('message','すでに休憩中です。');
        }
        $attendance->breakTimes()->create([
            'clock_in' => now(),
        ]);
        return redirect()->route('user.attendance');
        
    }
    public function breakEnd()
    {
        $user = Auth::user();
        $attendance = Attendance::where('user_id', $user->id)
        ->where('date', now()->toDateString())
        ->first();
        $lastBreak = $attendance->breakTimes()->latest()->first();

        if(!$lastBreak || $lastBreak->clock_out) {
            return redirect()->back()->with('message','休憩開始が記録されていません。');
        }
        
        
        $lastBreak->update([
            'clock_out' => now(),
        ]);
        
        return redirect()->route('user.attendance');

    }
    public function update(Request $request , $id) 
    {
       $user = Auth::user();
       $attendance = Attendance::with('breakTimes')->findOrFail($id);
        if ($attendance->user_id !== $user->id) {
        abort(403, '権限がありません');
        }
         // 出退勤の修正申請
         $clockIn = $request->input('clock_in');
         $clockOut = $request->input('clock_out');
         $targetDate = $attendance->date;
         $reason = $request->input('reason');
         if($clockIn || $clockOut || $reason) {
            AttendanceEdit::create([
                'attendance_id' => $attendance->id,
                'user_id' => $user->id,
                'request_date' => now(),
                'target_date' => $targetDate,
                'new_clock_in' => $clockIn ? Carbon::parse("$targetDate $clockIn") : null,
                'new_clock_out' => $clockOut ? Carbon::parse("$targetDate $clockOut") : null,
                'reason' => $reason,
            ]);
         }
         // 休憩の修正申請
        //  'breaks', []はどういう意味？

         $breaks = $request->input('breaks', []);

         foreach($breaks as $break)
        //  書き方がわからん。意味も
            {
                $newIn = $break['clock_in'] ?? null;
                $newOut = $break['clock_out'] ?? null;
                $reason = $request->input('reason');
                if (!$newIn && !$newOut) continue;
                 $targetDate = $attendance->date;
                 $newClockIn = $newIn ? Carbon::parse("$targetDate $newIn") : null;
                 $newClockOut = $newOut ? Carbon::parse("$targetDate $newOut") : null;
                // 既存の休憩修正（IDがある場合）
                if(!empty($break['id'])) {
                    BreakTimeEdit::create([
                        'break_time_id' => $break['id'],
                        'user_id' => $user->id,
                        'request_date' => now()->toDateString(),
                        'target_date' => $targetDate,
                        'new_clock_in' => $newClockIn,
                        'new_clock_out' => $newClockOut,
                        'reason' => $reason,
                        ]);
                    }
                else {
                    BreakTimeEdit::create([
                        'break_time_id' => null,
                        'user_id' => $user->id,
                        'request_date' => now()->toDateString(),
                        'target_date' => $targetDate,
                        'new_clock_in' => $newClockIn,
                        'new_clock_out' => $newClockOut,
                        'reason' => $reason,
                    ]);
                }
            }
            return redirect()->route('attendance.editRequest', ['id' => $attendance->id]
                
            );

    }
    
}
