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
         $newClockIn = $request->input('clock_in');
         $newClockOut = $request->input('clock_out');
         $defaultClockIn = optional($attendance)->clock_in;
         $defaultClockOut = optional($attendance)->clock_out;
         $targetDate = $attendance->date;
         $now = now();
         $reason = $request->input('reason');

         $isClockInChanged = $newClockIn && Carbon::parse($newClockIn)->format('H:i') !== optional($defaultClockIn)->format('H:i');
        $isClockOutChanged = $newClockOut && Carbon::parse($newClockOut)->format('H:i') !== optional($defaultClockOut)->format('H:i');

         if($isClockInChanged || $isClockOutChanged) {
            AttendanceEdit::create([
                'attendance_id' => $attendance->id ?? null,
                'user_id' => $user->id,
                'request_date' => $now,
                'target_date' => $targetDate,
                'new_clock_in' => $isClockInChanged ? Carbon::parse("$targetDate $newClockIn") : null,
                'new_clock_out' => $isClockOutChanged ? Carbon::parse("$targetDate $newClockOut") : null,
                
                'reason' => $reason,
            ]);
         }
         // 休憩の修正申請
        

         $breaks = $request->input('breaks', []);

         foreach($breaks as $i => $break)
        
            {
                $defaultIn = optional($attendance->breakTimes[$i] ?? null)->clock_in;
                $defaultOut = optional($attendance->breakTimes[$i] ?? null)->clock_out;

                $newIn = $break['clock_in'] ?? null;
                $newOut = $break['clock_out'] ?? null;

                $isBreakInChanged = $newIn && Carbon::parse($newIn)->format('H:i') !== optional($defaultIn)->format('H:i');
                $isBreakOutChanged = $newOut && Carbon::parse($newOut)->format('H:i') !== optional($defaultOut)->format('H:i');
                
                if ($isBreakInChanged || $isBreakOutChanged) {
                    BreakTimeEdit::create([
                        'break_time_id' => $break['id'] ?? null,
                        'user_id' => $user->id,
                       
                        'request_date' => $now,
                        'target_date' => $targetDate,
                        'new_clock_in' => $isBreakInChanged ? Carbon::parse("$targetDate $newIn") : null,
                        'new_clock_out' => $isBreakOutChanged ? Carbon::parse("$targetDate $newOut") : null,
                        'reason' => $reason,
                        ]);
                    }
                 }
               return redirect()->route('attendance.editRequest');

    }
    
   public function editRequest(Request $request)
     {
        $tab = $request->query('tab', 'waiting');
        
        // 「承認待ち」なら approved_at が null のものを取得
        $isWaiting = $tab === 'waiting';
        $userId = Auth::id();
        // 出退勤申請の取得
        $attendanceEdits = AttendanceEdit::with(['user','attendance'])->where('user_id',$userId)
        ->when($isWaiting, fn($q) => $q->whereNull('approved_at'))
        ->get();
        
        foreach ($attendanceEdits as $edit) {
            $original = $edit->attendance;
            if (!$original) continue;
            //  \Log::debug("Edit ID {$edit->id}");
            //  \Log::debug("original: " . optional($original->clock_in)->format('H:i'));
            // \Log::debug("new: " . optional($edit->new_clock_in)->format('H:i'));
            $attendanceEdits = $attendanceEdits->filter(function ($edit) {
            $original = $edit->attendance;
            if (!$original) return false;

            return (
                    ($edit->new_clock_in && !optional($original->clock_in)->eq($edit->new_clock_in)) ||
                    ($edit->new_clock_out && !optional($original->clock_out)->eq($edit->new_clock_out))
                    );
            });
        }


            // 休憩申請の取得
            $breakTimeEdits = BreakTimeEdit::with(['user', 'breakTime'])
            ->when($isWaiting, fn($q) => $q->whereNull('approved_at'))
            ->where('user_id', $userId) // ★ログインユーザーだけ
            ->get()
            ->filter(function ($edit) {
            $original = $edit->breakTime;
             if (!$original) return false;
                    return (
                        ($edit->new_clock_in && optional($original->clock_in)->format('H:i') !== optional($edit->new_clock_in)->format('H:i')) ||
                        ($edit->new_clock_out && optional($original->clock_out)->format('H:i') !== optional($edit->new_clock_out)->format('H:i'))
                    );
                });
           
        // 両方を合体

        $datas = $attendanceEdits->map(function ($edit) {
        return [
            'type' => 'attendance',
            'id' => $edit->id,
            'user' => $edit->user,
            'target_date' => $edit->target_date,
            'request_date' => $edit->request_date,
            'reason' => $edit->reason,
            'approved_at' => $edit->approved_at,
        ];
        })->merge($breakTimeEdits->map(function ($edit) {
        return [
            'type' => 'break',
            'id' => $edit->id,
            'user' => $edit->user,
            'target_date' => $edit->target_date,
            'request_date' => $edit->request_date,
            'reason' => $edit->reason,
            'approved_at' => $edit->approved_at,
        ];
        }))->sortByDesc('request_date');

        return view('attendance.edit', [
        'datas' => $datas,
        'tab' => $tab
        ]);
    }
 public function editDetail($id)
{
    // 1. 出退勤修正データを優先的に取得
    $attendanceEdit = \App\Models\AttendanceEdit::with('attendance.user')->find($id);
    // attendance.userはなんのこと？\App\Models\AttendanceEditはAttendanceでいかんのか？

    if ($attendanceEdit) {
        $attendance = $attendanceEdit->attendance;
        $user = $attendance->user;
        $date = $attendance->date;

        $clockIn = $attendanceEdit->new_clock_in ?? $attendance->clock_in;
        $clockOut = $attendanceEdit->new_clock_out ?? $attendance->clock_out;
// 修正してなくてもattendance_editsにはデフォルト値がはいっているが。$attendanceEdit->new_clock_inこれだけではだめ？
        $breakTimes = $attendance->breakTimes;
        $breakEdits = \App\Models\BreakTimeEdit::where('user_id', $user->id)
                            ->where('target_date', $date)->get();
                            // このこーどはいる？

        $reason = $attendanceEdit->reason;

    } else {
        // 2. 休憩だけの申請のとき
        $breakEdit = \App\Models\BreakTimeEdit::with('user')->findOrFail($id);
        $user = $breakEdit->user;
        $date = $breakEdit->target_date;

        $attendance = \App\Models\Attendance::with('breakTimes')->where('user_id', $user->id)
                        ->where('date', $date)->firstOrFail();

        $clockIn = $attendance->clock_in;
        $clockOut = $attendance->clock_out;

        $breakTimes = $attendance->breakTimes;
        $breakEdits = \App\Models\BreakTimeEdit::where('user_id', $user->id)
                            ->where('target_date', $date)->get();

        $reason = $breakEdit->reason;
    }

    $year = \Carbon\Carbon::parse($date)->format('Y年');
    $monthDay = \Carbon\Carbon::parse($date)->format('m月d日');

    return view('attendance.approve', compact(
        'user',
        'year',
        'monthDay',
        'clockIn',
        'clockOut',
        'breakTimes',
        'breakEdits',
        'reason'
    ));

}
 


 
}
