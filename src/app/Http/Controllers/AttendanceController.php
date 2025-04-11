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
        // 空文字は null に変換（削除扱い）
        // $newClockIn = $newClockIn === '' ? null : $newClockIn;
        // $newClockOut = $newClockOut === '' ? null : $newClockOut;

         
         $defaultClockIn = optional($attendance)->clock_in;
         $defaultClockOut = optional($attendance)->clock_out;
         $targetDate = $attendance->date;
         $now = now();
         $reason = $request->input('reason');
        // 出勤・退勤の変更判定（時間のみ比較）
        $isClockInChanged = $newClockIn !== null && $defaultClockIn && Carbon::parse($defaultClockIn)->format('H:i') !== $newClockIn;
        $isClockOutChanged = $newClockOut !== null && $defaultClockOut && Carbon::parse($defaultClockOut)->format('H:i') !== $newClockOut;
        $isClockInDeleted = $newClockIn === null && $defaultClockIn !== null;
        $isClockOutDeleted = $newClockOut === null && $defaultClockOut !== null;



        //  デフォルト値が入るので下のように修正
        // null になったことも「変更」と判定
        // $isClockInChanged =
        // ($newClockIn !== null && !optional($defaultClockIn)->eq(Carbon::parse("$targetDate $newClockIn")))
        // || ($newClockIn === null && $defaultClockIn !== null);

        // $isClockOutChanged =
        // ($newClockOut !== null && !optional($defaultClockOut)->eq(Carbon::parse("$targetDate $newClockOut")))
        // || ($newClockOut === null && $defaultClockOut !== null);





        // $isClockInChanged = $newClockIn && !optional($defaultClockIn)->eq(Carbon::parse($targetDate . ' ' . $newClockIn));
        // $isClockOutChanged = $newClockOut && !optional($defaultClockOut)->eq(Carbon::parse($targetDate . ' ' . $newClockOut));

        //  $isClockInChanged = $newClockIn && Carbon::parse($newClockIn)->format('H:i') !== optional($defaultClockIn)->format('H:i');
        // $isClockOutChanged = $newClockOut && Carbon::parse($newClockOut)->format('H:i') !== optional($defaultClockOut)->format('H:i');

         /*if($isClockInChanged || $isClockOutChanged) {
            AttendanceEdit::create([
                'attendance_id' => $attendance->id ?? null,
                'user_id' => $user->id,
                'request_date' => $now,
                'target_date' => $targetDate,

                // 以下に変更
                'new_clock_in' => $isClockInChanged ? ($newClockIn ? Carbon::parse("$targetDate $newClockIn") : null) : null,
                'new_clock_out' => $isClockOutChanged ? ($newClockOut ? Carbon::parse("$targetDate $newClockOut") : null) : null,
                */

                /*'new_clock_in' => $newClockIn ? Carbon::parse("$targetDate $newClockIn") : null,
                'new_clock_out' => $newClockOut ? Carbon::parse("$targetDate $newClockOut") : null,
                */
                /*'new_clock_in' => $isClockInChanged ? Carbon::parse("$targetDate $newClockIn") : null,
                'new_clock_out' => $isClockOutChanged ? Carbon::parse("$targetDate $newClockOut") : null,
                */
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
         // 休憩の修正申請
        

         $breaks = $request->input('breaks', []);
         $breakTimeMap = $attendance->breakTimes->keyBy('id'); // ← IDで紐付け！
         foreach($breaks as $break)
       
        
            {
                $breakId = $break['id'] ?? null;
                // 下のコードを追加したので
               /* $defaultBreak = $breakTimeMap[$breakId] ?? null;
                // 以下のように修整
                $defaultIn = optional($defaultBreak)->clock_in;
                $defaultOut = optional($defaultBreak)->clock_out;
                */
                /*$defaultIn = optional($breakTimeMap[$breakId] ?? null)->clock_in;
                $defaultOut = optional($breakTimeMap[$breakId] ?? null)->clock_out;
                */
                /*$defaultIn = optional($attendance->breakTimes[$i] ?? null)->clock_in;
                $defaultOut = optional($attendance->breakTimes[$i] ?? null)->clock_out;
                */
                $newIn = $break['clock_in'] ?? null;
                $newOut = $break['clock_out'] ?? null;
                // 新規追加の休憩（break_idがない）
            if ($breakId === null) {
        // どちらか入力されていれば登録
                if ($newIn !== null || $newOut !== null) {
                BreakTimeEdit::create([
                'break_time_id' => null, // 新規なのでnull
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
    
//  既存の休憩：修正 or 削除のチェック
    $defaultBreak = $attendance->breakTimes->firstWhere('id', $breakId);
    $defaultIn = optional($defaultBreak)->clock_in;
    $defaultOut = optional($defaultBreak)->clock_out;
    
                // 修正
                // 一瞬コメントアウトするけどうまくいかんかったら戻す
                /*$newIn = $newIn === '' ? null : $newIn;
                $newOut = $newOut === '' ? null : $newOut;
                */
                // いかのようにしゅうせい
                $isBreakInChanged = $newIn !== null && $defaultIn && Carbon::parse($defaultIn)->format('H:i') !== $newIn;
                $isBreakOutChanged = $newOut !== null && $defaultOut && Carbon::parse($defaultOut)->format('H:i') !== $newOut;
                $isBreakDeleted = $newIn === null && $newOut === null && ($defaultIn || $defaultOut);

                /*$isBreakInChanged =
            ($newIn !== null && !optional($defaultIn)->eq(Carbon::parse("$targetDate $newIn")))
            || ($newIn === null && $defaultIn !== null);

                $isBreakOutChanged =
            ($newOut !== null && !optional($defaultOut)->eq(Carbon::parse("$targetDate $newOut")))
            || ($newOut === null && $defaultOut !== null);
*/
                /*$isBreakInChanged = $newIn && !optional($defaultIn)->eq(Carbon::parse($targetDate . ' ' . $newIn));
                $isBreakOutChanged = $newOut && !optional($defaultOut)->eq(Carbon::parse($targetDate . ' ' . $newOut));
                */
                // $isBreakInChanged = $newIn && Carbon::parse($newIn)->format('H:i') !== optional($defaultIn)->format('H:i');
                // $isBreakOutChanged = $newOut && Carbon::parse($newOut)->format('H:i') !== optional($defaultOut)->format('H:i');
                
                if ($isBreakInChanged || $isBreakOutChanged || $isBreakDeleted) {
                    BreakTimeEdit::create([
                        // 'break_time_id' => $break['id'] ?? null,
                        // nullにしていいのか？
                        'break_time_id' => $breakId ,

                        'user_id' => $user->id,
                       
                        'request_date' => $now,
                        'target_date' => $targetDate,
                        // 修正してみた
                        /*'new_clock_in' => $isBreakInChanged ? ($newIn ? Carbon::parse("$targetDate $newIn") : null) : null,
                        'new_clock_out' => $isBreakOutChanged ? ($newOut ? Carbon::parse("$targetDate $newOut") : null) : null,
                        */
                        'new_clock_in' => $isBreakInChanged ? Carbon::parse("$targetDate $newIn") : null,
                        'new_clock_out' => $isBreakOutChanged ? Carbon::parse("$targetDate $newOut") : null,
                        
                        // 'new_clock_in' => $newIn ? Carbon::parse("$targetDate $newIn") : null,
                        // 'new_clock_out' => $newOut ? Carbon::parse("$targetDate $newOut") : null,
                
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
        // あとでチャットに確認
        $attendanceEdits = AttendanceEdit::with(['user','attendance'])->where('user_id',$userId)
        ->when($isWaiting, fn($q) => $q->whereNull('approved_at'))
        ->get();
        // この下のコードを入れたら、修正したものだけ表示されるようになった！
        foreach ($attendanceEdits as $edit) {
            $original = $edit->attendance;
            if (!$original) continue;
            
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
                // $datas = $attendanceEdits->map(function ($edit) {の書き方と意味ががわからない

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
        // ここをtarget_dateにしてもいいの？

        return view('attendance.edit', [
        'datas' => $datas,
        'tab' => $tab
        ]);
    }
 public function editDetail($id)

{
    // 1. 出退勤修正データを優先的に取得
    $attendanceEdit = AttendanceEdit::with('attendance.user')->find($id);
    

    if ($attendanceEdit) {
        $attendance = $attendanceEdit->attendance;
        $user = $attendance->user;
        $date = $attendance->date;

        $clockIn = $attendanceEdit->new_clock_in ?? $attendance->clock_in;
        $clockOut = $attendanceEdit->new_clock_out ?? $attendance->clock_out;

        $breakTimes = $attendance->breakTimes;
        $breakEdits = BreakTimeEdit::where('user_id', $user->id)
                            ->where('target_date', $date)->get();
                            

        $reason = $attendanceEdit->reason;

    } else {
        // 2. 休憩だけの申請のとき
        $breakEdit = BreakTimeEdit::with('user')->findOrFail($id);
        $user = $breakEdit->user;
        $date = $breakEdit->target_date;

        $attendance = Attendance::with('breakTimes')->where('user_id', $user->id)
                        ->where('date', $date)->firstOrFail();

        $clockIn = $attendance->clock_in;
        $clockOut = $attendance->clock_out;

        $breakTimes = $attendance->breakTimes;
        $breakEdits = BreakTimeEdit::where('user_id', $user->id)
                            ->where('target_date', $date)->get();
                          

        $reason = $breakEdit->reason;
    }
     // ⭐ ここが重要：表示用の休憩マージ処理
     $breakEditMap = $breakEdits->keyBy('break_time_id');
    $mergedBreaks = [];

   
        foreach ($breakTimes as $breakTime) {
            $edit = $breakEditMap->get($breakTime->id);
            if ($edit) {
    // 修正 or 削除申請がある場合
                if (is_null($edit->new_clock_in) && is_null($edit->new_clock_out)) {
                    continue;
                }
        // 明示的な削除申請
                // $breakClockIn = null;
                // $breakClockOut = null;
                // } else {
        // 修正申請（どちらかがある）
                    $breakClockIn = $edit && $edit->new_clock_in ? $edit->new_clock_in : $breakTime->clock_in;
                    $breakClockOut = $edit && $edit->new_clock_out ? $edit->new_clock_out : $breakTime->clock_out; 
                // }
         } else {
    // 申請がない場合（元データそのまま表示）
                    $breakClockIn = $breakTime->clock_in;
                    $breakClockOut = $breakTime->clock_out;
                }
        // }


            // 削除申請ならスキップ
            /*if ($edit && ($edit->new_clock_in === null && $edit->new_clock_out === null)) {
            $mergedBreaks[] = [
                'clock_in' => null,
                'clock_out' => null,
                ];
            continue;
            }
            // 通常の修正 or 元データを表示
            $breakClockIn = $edit && $edit->new_clock_in ? $edit->new_clock_in : $breakTime->clock_in;
            $breakClockOut = $edit && $edit->new_clock_out ? $edit->new_clock_out : $breakTime->clock_out;
            */
            
            
            $mergedBreaks[] = [
            'clock_in' => $breakClockIn,
            'clock_out' => $breakClockOut,
            ];
        }      
        


           
    
    //    dd($mergedBreaks);
     $extraEdits = $breakEdits->filter(fn($e) => is_null($e->break_time_id));
        foreach ($extraEdits as $edit) {
        $mergedBreaks[] = [
            'clock_in' => $edit->new_clock_in,
            'clock_out' => $edit->new_clock_out,
            ];
        }



    $year = Carbon::parse($date)->format('Y年');
    $monthDay = Carbon::parse($date)->format('m月d日');
    $breakEdits = BreakTimeEdit::where('user_id', $user->id)->where('target_date', $date)->get();
        //  dd($breakEdits);                  

    return view('attendance.approve', compact(
        'user',
        'year',
        'monthDay',
        'clockIn',
        'clockOut',
        'breakTimes',
        'breakEdits',
        'reason',
        'attendance',
        'mergedBreaks',
    ));

}
 


 
}

