<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceEdit;
use App\Models\BreakTimeEdit;
use Carbon\Carbon;
use App\Http\Requests\UserAttendanceRequest;

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
    
    public function update(UserAttendanceRequest $request , $id) 
    {
       $user = Auth::user();
       $attendance = Attendance::with('breakTimes')->findOrFail($id);
        if ($attendance->user_id !== $user->id) {
        abort(403, '権限がありません');
        }
        
         
         $newClockIn = $request->input('clock_in');
         $newClockOut = $request->input('clock_out');
        
        $defaultClockIn = optional($attendance)->clock_in;
         $defaultClockOut = optional($attendance)->clock_out;
         $targetDate = $attendance->date;
         $now = now();
         $reason = $request->input('reason');
        
        $isClockInChanged = $newClockIn !== null && (
            $defaultClockIn === null || Carbon::parse($defaultClockIn)->format('H:i') !== $newClockIn
        );
            $isClockOutChanged = $newClockOut !== null && (
            $defaultClockOut === null || Carbon::parse($defaultClockOut)->format('H:i') !== $newClockOut
        );


       
        $isClockInDeleted = $newClockIn === null && $defaultClockIn !== null;
        $isClockOutDeleted = $newClockOut === null && $defaultClockOut !== null;

        if ($isClockInChanged || $isClockOutChanged || $isClockInDeleted || $isClockOutDeleted) {
                AttendanceEdit::create([
                'attendance_id' => $attendance->id,
                'user_id' => $user->id,
                'request_date' => $now,
                'target_date' => $targetDate,
                'new_clock_in' => $isClockInChanged ? Carbon::parse("$targetDate $newClockIn") : null,
                'new_clock_out' => $isClockOutChanged ? Carbon::parse("$targetDate $newClockOut") : null,
                'reason' => $reason,
            ]);
         }
         
        $breaks = $request->input('breaks', []);
       
        foreach($breaks as $break)
       {
            $breakId = $break['id'] ?? null;
            $newIn = $break['clock_in'] ?? null;
            $newOut = $break['clock_out'] ?? null;
               
            if ($breakId === null) {
        
                if ($newIn !== null || $newOut !== null) {
                BreakTimeEdit::create([
                'break_time_id' => null, 
                'user_id' => $user->id,
                'request_date' => $now,
                'target_date' => $targetDate,
                'new_clock_in' => $newIn ? Carbon::parse("$targetDate $newIn") : null,
                'new_clock_out' => $newOut ? Carbon::parse("$targetDate $newOut") : null,
                'reason' => $reason,
            ]);
        }
        continue;
    }
  
            $defaultBreak = $attendance->breakTimes->firstWhere('id', $breakId);
            $defaultIn = optional($defaultBreak)->clock_in;
            $defaultOut = optional($defaultBreak)->clock_out;
    
            $isBreakInChanged = $newIn !== null && $defaultIn && Carbon::parse($defaultIn)->format('H:i') !== $newIn;
            $isBreakOutChanged = $newOut !== null && $defaultOut && Carbon::parse($defaultOut)->format('H:i') !== $newOut;
            $isBreakDeleted = $newIn === null && $newOut === null && ($defaultIn || $defaultOut);

             if ($isBreakInChanged || $isBreakOutChanged || $isBreakDeleted) {
                    BreakTimeEdit::create([
                        'break_time_id' => $breakId ,
                        'user_id' => $user->id,
                        'request_date' => $now,
                        'target_date' => $targetDate,
                        'new_clock_in' => $isBreakInChanged ? Carbon::parse("$targetDate $newIn") : null,
                        'new_clock_out' => $isBreakOutChanged ? Carbon::parse("$targetDate $newOut") : null,
                        'reason' => $reason,
                        ]);
                    }
                 }
               return redirect()->route('user.stamp_correction_request.list',['tab' => 'waiting']);

    }
    public function store(UserAttendanceRequest $request)
    {
       $user = Auth::user();
       $targetDate = $request->input('date');
       $now = now();
       $reason = $request->input('reason');
       $newClockIn = $request->input('clock_in');
       $newClockOut = $request->input('clock_out');
       
       if($newClockIn || $newClockOut) {
        AttendanceEdit::create([
             'attendance_id' => null, 
            'user_id' => $user->id,
            'request_date' => $now,
            'target_date' => $targetDate,
            'new_clock_in' => $newClockIn ? Carbon::parse("$targetDate $newClockIn") : null,
            'new_clock_out' => $newClockOut ? Carbon::parse("$targetDate $newClockOut") : null,
            'reason' => $reason,
        ]);
       }
       $breaks = $request->input('breaks', []);

       foreach($breaks as $break) {
        $newIn = $break['clock_in'] ?? null;
        $newOut = $break['clock_out'] ?? null;

        if($newIn || $newOut) {
            BreakTimeEdit::create([
                'break_time_id' => null,
                'user_id' => $user->id,
                'request_date' => $now,
                'target_date' => $targetDate,
                'new_clock_in' => $newIn ? Carbon::parse("$targetDate $newIn") : null,
                'new_clock_out' => $newOut ? Carbon::parse("$targetDate $newOut") : null,
                'reason' => $reason,
            ]);
        }
       }
       return redirect()->route('user.stamp_correction_request.list',['tab' => 'waiting']);
    }
       
}   