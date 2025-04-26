<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceEdit;
use App\Models\BreakTimeEdit;
use App\Models\User;
use Carbon\Carbon;
use App\Http\Requests\AttendanceRequest;

class AdminController extends AttendanceDetailController
{
    
    public function staffList()
    {
        // $admin = Auth::guard('admin')->user();
         $user = Auth::guard('web')->user();
        $users = User::where('role', 'user')
        ->select(['id','name','email'])->get();


        return view ('admin.staff-list',compact('users'));
    }
    public function showList(Request $request,$id) {
        $admin = Auth::guard('admin')->user();
        // 管理者用の処理
        // $user = Auth::guard('web')->user();
        // $user = Auth::user();
        $user = User::findOrFail($id);

        $monthParam = $request->query('month');
        
        $targetMonth = $monthParam ? Carbon::parse($monthParam . '-01'): now();

        $thisMonth = $targetMonth->format('Y/m');
        
        $previousMonth = $targetMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $targetMonth->copy()->addMonth()->format('Y-m');

        $startOfMonth = $targetMonth->copy()->startOfMonth();
        $endOfMonth = $targetMonth->copy()->endOfMonth();
        // 全日付を作成

        $dates = [];
        $currentDate = $startOfMonth->copy();
        while ($currentDate <= $endOfMonth) {
            $dates[] = $currentDate->copy();
            $currentDate->addDay();
        }
        // 勤怠データをまとめて取得
        
        $attendances = Attendance::with('breakTimes')
        ->where('user_id',$user->id)
        //  いるの、これ？
        ->WhereBetween('date', [$startOfMonth, $endOfMonth])
        ->get()
        ->keyBy(function($item) {
            return Carbon::parse($item->date)->format('Y-m-d');
        });
        $weekMap = [
            'Sun' => '日', 'Mon' => '月', 'Tue' => '火',
            'Wed' => '水', 'Thu' => '木', 'Fri' => '金', 'Sat' => '土',
        ];
        
        $attendanceData = [];
        foreach ($dates as $date) {
            $dateKey = $date->format('Y-m-d');
            $data = $attendances->get($dateKey);
            
            $clockIn = optional($data)->clock_in ? Carbon::parse($data->clock_in) : null;
            $clockOut = optional($data)->clock_out ? Carbon::parse($data->clock_out) : null;

            // 休憩時間の合計（分単位）
            $totalBreakMinutes = 0;
            if($data && $data->breakTimes) {
                foreach($data->breakTimes as $break_time) {
                    $breakStart = Carbon::parse($break_time->clock_in);
                    $breakEnd = Carbon::parse($break_time->clock_out);
                    
                    $totalBreakMinutes += $breakStart->diffInMinutes($breakEnd);
                }
            }
             
        $workingMinutes = 0;
            if ($clockIn && $clockOut) {
            $workingMinutes = $clockIn->diffInMinutes($clockOut) - $totalBreakMinutes;
            }

            // 表示用データに整形
            $attendanceData[] = [
                
                 'id'=> optional($data)->id ?? 'date-' . $date->format('Ymd'),
                'raw_date' => $date->format('Y-m-d'),
                'date' => $date->format('m/d') . '(' . $weekMap[$date->format('D')] . ')' ,
                'clockIn' => $clockIn ? $clockIn->format('H:i') : '',
                'clockOut' => $clockOut ? $clockOut->format('H:i') : '',
                'breakTime' => ($clockIn && $clockOut) ? $this->formatMinutes($totalBreakMinutes) : '',
                'workingTime' => ($clockIn && $clockOut) ?$this->formatMinutes($workingMinutes) : '',
            ];
        }
        
        return view ('admin.month-list' ,compact('thisMonth','previousMonth','nextMonth','attendanceData','user'));
    }
        private function formatMinutes($minutes) {
        $hours = floor($minutes/ 60);
        $mins = $minutes % 60;
        return sprintf('%d:%02d', $hours, $mins);
    }

    public function detailForAdmin($id) {
        $admin = Auth::guard('admin')->user();
        $attendance = Attendance::with('breakTimes')->findOrFail($id);

        

        $date = Carbon::parse($attendance->date);
        $year = $date->format('Y');
        $monthDay = $date->format('n月j日');
        return view('admin.detail',compact('attendance','year','monthDay'));

    }
    public function detailByDateForAdmin($id,$date)
    {
        // $admin = Auth::guard('admin')->user(); // 管理者認証（使うなら）
        // 対象ユーザーの取得
    $user = User::findOrFail($id);

    // 該当する勤怠データを取得（なければ null）
    $attendance = Attendance::with('breakTimes')
         ->where('user_id', $user->id)
        ->whereDate('date', $date)
        ->first();

    // データがない場合は空の Attendance オブジェクトを作成して渡す（修正申請の入力用）
    if (!$attendance) {
        $attendance = new Attendance([
            'user_id' => $user->id,
            'date' => $date,
            'clock_in' => null,
            'clock_out' => null,
        ]);
        $attendance->breakTimes = collect(); // 空のコレクションを渡す
    }

    // 年・日付表示用に整形
    $carbonDate = \Carbon\Carbon::parse($date);
    $year = $carbonDate->format('Y');
    $monthDay = $carbonDate->format('n月j日');
    // raw_date をここで定義（ビューで詳細リンクに使う用）
    $raw_date = $carbonDate->format('Y-m-d');

    return view('admin.detail', compact('attendance', 'year', 'monthDay','user','raw_date'));
}

    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        // 管理者用の処理
        // $user = User::findOrFail($id);
        // 日次なので以下のようにした
        $dayParam = $request->query('day');
        
        $targetDay = $dayParam ? Carbon::parse($dayParam . '-01'): now();
        $today = $targetDay->isoFormat('YYYY年M月D日');

        $thisDay = $targetDay->format('Y/m/d');
        
        $previousDay = $targetDay->copy()->subDay()->format('Y-m-d');
        $nextDay = $targetDay->copy()->addDay()->format('Y-m-d');
        // 勤怠データをまとめて取得
        
        $attendances = Attendance::with(['breakTimes','user'])
        // ->where('user_id',$user->id)
        //  いるの、これ？
         ->WhereDate('date', $targetDay)
        ->get();
        $attendanceData = [];
         foreach ($attendances as $attendance) {
             $clockIn = $attendance->clock_in ? Carbon::parse($attendance->clock_in) : null;
            $clockOut = $attendance->clock_out ? Carbon::parse($attendance->clock_out) : null;
          
            // 休憩時間の合計（分単位）
            $totalBreakMinutes = 0;
            foreach($attendance->breakTimes as $break_time) {
                    $breakStart = Carbon::parse($break_time->clock_in);
                    $breakEnd = Carbon::parse($break_time->clock_out);
                    
                    $totalBreakMinutes += $breakStart->diffInMinutes($breakEnd);
                }
            $workingMinutes = 0;
                if ($clockIn && $clockOut) {
                $workingMinutes = $clockIn->diffInMinutes($clockOut) - $totalBreakMinutes;
                }

            // 表示用データに整形
            $attendanceData[] = [
                'user_name' => $attendance->user->name,
                'id' => $attendance->id,
                'clockIn' => $clockIn ? $clockIn->format('H:i') : '',
                'clockOut' => $clockOut ? $clockOut->format('H:i') : '',
                'breakTime' => ($clockIn && $clockOut) ? $this->formatMinutes($totalBreakMinutes) : '',
                'workingTime' => ($clockIn && $clockOut) ?$this->formatMinutes($workingMinutes) : '',
            ];
         }
        
        return view ('admin.list' ,compact('thisDay','previousDay','nextDay','today','attendanceData',));
    }

public function update(AttendanceRequest $request, $id)
{
    $admin = Auth::guard('admin')->user();
    $attendance = Attendance::with('breakTimes')->findOrFail($id);
    $user = $attendance->user;

    $now = now();
    $reason = $request->input('reason');

    // 出勤退勤データ
    $newClockIn = $request->input('clock_in') !== '' ? $request->input('clock_in') : null;
    $newClockOut = $request->input('clock_out') !== '' ? $request->input('clock_out') : null;

    // 日付処理
    $year = $request->input('target_year');
    $month = $request->input('target_month');
    $day = $request->input('target_day');

    try {
        $targetDate = Carbon::createFromDate($year, $month, $day);
        $formattedDate = $targetDate->format('Y-m-d');
    } catch (\Exception $e) {
        return back()->withErrors(['target_date' => '日付が正しくありません']);
    }

    $defaultClockIn = optional($attendance)->clock_in;
    $defaultClockOut = optional($attendance)->clock_out;
    $originalDate = Carbon::parse($attendance->date)->format('Y-m-d');

    $isClockInChanged = $newClockIn !== null && ($defaultClockIn === null || Carbon::parse($defaultClockIn)->format('H:i') !== $newClockIn);
    $isClockOutChanged = $newClockOut !== null && ($defaultClockOut === null || Carbon::parse($defaultClockOut)->format('H:i') !== $newClockOut);
    $isClockInDeleted = $newClockIn === null && $defaultClockIn !== null;
    $isClockOutDeleted = $newClockOut === null && $defaultClockOut !== null;
    $isDateChanged = $formattedDate !== $originalDate;

    // \u3010\u65e5\u4ed8\u5909\u66f4\u3042\u308a\u306a\u3089\u5148\u306b\u79fb\u52d5\u5148\u3092\u6d88\u3059
    if ($isDateChanged) {
        Attendance::where('user_id', $user->id)
            ->where('date', $formattedDate)
            ->where('id', '!=', $attendance->id)
            ->delete();

        BreakTime::whereHas('attendance', function ($query) use ($user, $formattedDate, $attendance) {
            $query->where('user_id', $user->id)
                  ->where('date', $formattedDate)
                  ->where('id', '!=', $attendance->id);
        })->delete();
    }

    // AttendanceEdit\u306e\u767b\u9332
    if ($isClockInChanged || $isClockOutChanged || $isClockInDeleted || $isClockOutDeleted || $isDateChanged) {
        AttendanceEdit::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'request_date' => $now,
            'target_date' => $formattedDate,
            'new_clock_in' => $isClockInChanged ? Carbon::parse($formattedDate . ' ' . $newClockIn) : null,
            'new_clock_out' => $isClockOutChanged ? Carbon::parse($formattedDate . ' ' . $newClockOut) : null,
            'reason' => $reason,
            'edited_by_admin' => true,
        ]);
    }

    // Attendance\u306e\u672c\u4f53\u3092\u66f4\u65b0
    $attendance->date = $formattedDate;
    $attendance->clock_in = $newClockIn ? Carbon::parse($formattedDate . ' ' . $newClockIn) : null;
    $attendance->clock_out = $newClockOut ? Carbon::parse($formattedDate . ' ' . $newClockOut) : null;
    $attendance->save();

    // \u4f11\u61a9\u6642\u9593\u306e\u5909\u66f4
    $breaks = $request->input('breaks', []);
    $attendance->breakTimes()->delete(); // \u5148\u306b\u5168\u6d88\u3057

    foreach ($breaks as $break) {
        $newIn = trim($break['clock_in'] ?? '') ?: null;
        $newOut = trim($break['clock_out'] ?? '') ?: null;

        if ($newIn || $newOut) {
            BreakTimeEdit::create([
                'break_time_id' => null,
                'user_id' => $user->id,
                'request_date' => $now,
                'target_date' => $formattedDate,
                'new_clock_in' => $newIn ? Carbon::parse($formattedDate . ' ' . $newIn) : null,
                'new_clock_out' => $newOut ? Carbon::parse($formattedDate . ' ' . $newOut) : null,
                'reason' => $reason,
                'edited_by_admin' => true,
            ]);

            BreakTime::create([
                'attendance_id' => $attendance->id,
                'user_id' => $user->id,
                'clock_in' => $newIn ? Carbon::parse($formattedDate . ' ' . $newIn) : null,
                'clock_out' => $newOut ? Carbon::parse($formattedDate . ' ' . $newOut) : null,
            ]);
        }
    }

    return redirect()->route('admin.stamp_correction_request.list')
        ->with('message', '更新が完了しました。');
}


    /*２つ目のコード
    public function update(AttendanceRequest $request, $id)
{
    $admin = Auth::guard('admin')->user();
    $attendance = Attendance::with('breakTimes')->findOrFail($id);
    $user = $attendance->user;

    $now = now();
    $reason = $request->input('reason');

    $newClockIn = $request->input('clock_in');
    $newClockOut = $request->input('clock_out');
    $year = $request->input('target_year');
    $month = $request->input('target_month');
    $day = $request->input('target_day');

    try {
        $targetDate = Carbon::createFromDate($year, $month, $day);
        $formattedDate = $targetDate->format('Y-m-d');
    } catch (\Exception $e) {
        return back()->withErrors(['target_date' => '日付が正しくありません']);
    }

    // 出勤退勤データの変更チェック
    $defaultClockIn = optional($attendance)->clock_in;
    $defaultClockOut = optional($attendance)->clock_out;
    $originalDate = Carbon::parse($attendance->date)->format('Y-m-d');

    $isClockInChanged = $newClockIn !== null && ($defaultClockIn === null || Carbon::parse($defaultClockIn)->format('H:i') !== $newClockIn);
    $isClockOutChanged = $newClockOut !== null && ($defaultClockOut === null || Carbon::parse($defaultClockOut)->format('H:i') !== $newClockOut);
    $isClockInDeleted = $newClockIn === null && $defaultClockIn !== null;
    $isClockOutDeleted = $newClockOut === null && $defaultClockOut !== null;
    $isDateChanged = $formattedDate !== $originalDate;

    if ($isClockInChanged || $isClockOutChanged || $isClockInDeleted || $isClockOutDeleted || $isDateChanged) {
        // 変更があれば AttendanceEdit に履歴を作成
        AttendanceEdit::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'request_date' => $now,
            'target_date' => $formattedDate,
            'new_clock_in' => $isClockInChanged ? Carbon::parse($formattedDate . ' ' . $newClockIn) : null,
            'new_clock_out' => $isClockOutChanged ? Carbon::parse($formattedDate . ' ' . $newClockOut) : null,
            'reason' => $reason,
            'edited_by_admin' => true,
        ]);
    }
// もし日付が変更されていたら
if ($isDateChanged) {
    // 移動先の日付に、同じユーザーの別出勤データがあったら削除
    Attendance::where('user_id', $user->id)
        ->where('date', $formattedDate)
        ->where('id', '!=', $attendance->id) // 自分自身を除外
        ->delete();
    // 本番の Attendance を直接更新
    $attendance->date = $formattedDate;
    $attendance->clock_in = $newClockIn ? Carbon::parse($formattedDate . ' ' . $newClockIn) : null;
    $attendance->clock_out = $newClockOut ? Carbon::parse($formattedDate . ' ' . $newClockOut) : null;
    $attendance->save();

    // ===== 休憩時間の処理 =====
    $breaks = $request->input('breaks', []);

    // ① 既存の休憩をすべて削除
    $attendance->breakTimes()->delete();

    // ② 新しい休憩を登録
    foreach ($breaks as $break) {
        $newIn = trim($break['clock_in'] ?? '') ?: null;
        $newOut = trim($break['clock_out'] ?? '') ?: null;

        if ($newIn || $newOut) {
            // BreakTime 登録
            $newBreak = BreakTime::create([
                'attendance_id' => $attendance->id,
                'user_id' => $user->id,
                'clock_in' => $newIn ? Carbon::parse($formattedDate . ' ' . $newIn) : null,
                'clock_out' => $newOut ? Carbon::parse($formattedDate . ' ' . $newOut) : null,
            ]);

            // BreakTimeEdit 登録（管理者編集履歴）
            BreakTimeEdit::create([
                'break_time_id' => $newBreak->id,
                'user_id' => $user->id,
                'request_date' => $now,
                'target_date' => $formattedDate,
                'new_clock_in' => $newBreak->clock_in,
                'new_clock_out' => $newBreak->clock_out,
                'reason' => $reason,
                'edited_by_admin' => true,
            ]);
        }
    }

    return redirect()->route('admin.stamp_correction_request.list')->with('message', '更新が完了しました');
}
*/




     /*１つ目のコード　ちょっと動かした
     public function update(AttendanceRequest $request , $id) 
    {
        $admin = Auth::guard('admin')->user();
        $attendance = Attendance::with('breakTimes')->findOrFail($id);
       $user = $attendance->user;
       
         $now = now();
         $reason = $request->input('reason');
         $newClockIn = $request->input('clock_in');
        //  if ($newClockIn === '') {
            // $newClockIn = null;
            // }
         $newClockOut = $request->input('clock_out');
        //  if ($newClockOut === '') {
            // $newClockOut = null;
        // }
        $year = $request->input('target_year');
        $month = $request->input('target_month');
        $day = $request->input('target_day');

    try {
        $targetDate = Carbon::createFromDate($year, $month, $day);
        $formattedDate = $targetDate->format('Y-m-d'); // ← ここが重要！
        } catch (\Exception $e) {
        return back()->withErrors(['target_date' => '日付が正しくありません']);
        }
        

        $defaultClockIn = optional($attendance)->clock_in;
        $defaultClockOut = optional($attendance)->clock_out;
       $originalDate = Carbon::parse($attendance->date)->format('Y-m-d');

        $isClockInChanged = $newClockIn !== null && (
        $defaultClockIn === null || Carbon::parse($defaultClockIn)->format('H:i') !== $newClockIn
        );
        $isClockOutChanged = $newClockOut !== null && (
        $defaultClockOut === null || Carbon::parse($defaultClockOut)->format('H:i') !== $newClockOut
        );
        $isClockInDeleted = $newClockIn === null && $defaultClockIn !== null;
        $isClockOutDeleted = $newClockOut === null && $defaultClockOut !== null;
        // $originalDate = Carbon::parse($attendance->date)->format('Y-m-d');
        $isDateChanged = $formattedDate !== $originalDate;

        if ($isClockInChanged || $isClockOutChanged || $isClockInDeleted || $isClockOutDeleted || $isDateChanged) {
          if ($isDateChanged) {
        // 🆕 変更後の日付のデータを削除
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $formattedDate)
            ->first();

        if ($existingAttendance) {
            BreakTime::where('attendance_id', $existingAttendance->id)->delete();
            $existingAttendance->delete();
        }

        // 変更された日付を更新
        $attendance->date = $formattedDate;
    }

                AttendanceEdit::create([
                'attendance_id' => $attendance->id,
                'user_id' => $user->id,
                'request_date' => $now,
                'target_date' => $formattedDate,
                'new_clock_in' => $isClockInChanged ? Carbon::parse($formattedDate . ' ' . $newClockIn) : null,
                'new_clock_out' => $isClockOutChanged ? Carbon::parse($formattedDate . ' ' . $newClockOut) : null,
                'reason' => $reason,
                'edited_by_admin' => true,
            ]);
         }
        //  if分はどうなる？
         if(!is_null($newClockIn)) {
            $attendance->clock_in = Carbon::parse($formattedDate . ' ' . $newClockIn);
         }
         if(!is_null($newClockOut)) {
            $attendance->clock_out = Carbon::parse($formattedDate . ' ' . $newClockOut);
         }
         $attendance->save();
         // 休憩の修正申請
        $breaks = $request->input('breaks', []);
       
         // 既存の休憩を全部一旦削除してから
        $attendance->breakTimes()->delete();
         foreach($breaks as $break)
       {
        // これはどうなる？下のコード
            $breakId = $break['id'] ?? null;

            $newIn = trim($break['clock_in'] ?? ' ') ?: null;
            $newOut = trim($break['clock_out'] ?? ' ') ?: null;
                // 新規追加の休憩（break_idがない）
            if ($breakId === null) {
        // 両方入力されていれば新規登録
                if ($newIn !== null || $newOut !== null) {
                BreakTimeEdit::create([
                'break_time_id' => null, // 新規なのでnull
                'user_id' => $user->id,
                'request_date' => $now,
                'target_date' => $targetDate->format('Y-m-d'),
                'new_clock_in' => $newIn ? Carbon::parse($targetDate->format('Y-m-d') . ' ' . $newIn) : null,
                'new_clock_out' => $newOut ? Carbon::parse($targetDate->format('Y-m-d') . ' ' . $newOut) : null,
                'reason' => $reason,
                'edited_by_admin' => true,
            ]);
             // ② BreakTime作成（本データ）
                BreakTime::create([
                'attendance_id' => $attendance->id,
                'user_id' => $user->id,
                'clock_in' => Carbon::parse($targetDate->format('Y-m-d') . ' ' . $newIn),
                'clock_out' => Carbon::parse($targetDate->format('Y-m-d') . ' ' . $newOut),
            ]);
        }
        //  continue;
    }else {//  既存の休憩：修正 or 削除のチェック
            $defaultBreak = $attendance->breakTimes->firstWhere('id', $breakId);
            $defaultIn = optional($defaultBreak)->clock_in;
            $defaultOut = optional($defaultBreak)->clock_out;
    
        // 修正
                
                
            $isBreakInChanged = $newIn !== null && $defaultIn && Carbon::parse($defaultIn)->format('H:i') !== $newIn;
            $isBreakOutChanged = $newOut !== null && $defaultOut && Carbon::parse($defaultOut)->format('H:i') !== $newOut;
            $isBreakDeleted = $newIn === null && $newOut === null && ($defaultIn || $defaultOut);

             if ($isBreakInChanged || $isBreakOutChanged || $isBreakDeleted) {
                BreakTimeEdit::create([
                    'break_time_id' => $breakId ,
                    'user_id' => $user->id,
                    'request_date' => $now,
                    'target_date' => $targetDate->format('Y-m-d'),
                    'new_clock_in' => $isBreakInChanged ? Carbon::parse($targetDate->format('Y-m-d') . ' ' . $newIn) : null,
                    'new_clock_out' => $isBreakOutChanged ? Carbon::parse($targetDate->format('Y-m-d') . ' ' . $newOut) : null,
                    'reason' => $reason,
                    'edited_by_admin' => true,
                ]);

                if($defaultBreak) {
                    if($isBreakDeleted) {
                        $defaultBreak->delete();
                    } else {
                        $defaultBreak->clock_in = $newIn ? Carbon::parse($targetDate->format('Y-m-d') . ' ' . $newIn) : $defaultBreak->clock_in;
                        $defaultBreak->clock_out = $newOut ? Carbon::parse($targetDate->format('Y-m-d') . ' ' . $newOut) : $defaultBreak->clock_out;
                        $defaultBreak->save();
                    }
                }
            }
        }
    }
        return redirect()->route('admin.stamp_correction_request.list');

    }
        */
    public function store(AttendanceRequest $request,$id)
    {
         $admin = Auth::guard('admin')->user();
        //  $user = Auth::user();
        $user = User::findOrFail($id);
    //    $targetDate = $request->input('date');
       $now = now();
       $reason = $request->input('reason');
       $newClockIn = $request->input('clock_in');
       $newClockOut = $request->input('clock_out');
        $year = $request->input('target_year');
        $month = $request->input('target_month');
        $day = $request->input('target_day');
        try {
        $targetDate = Carbon::createFromDate($year, $month, $day);
        // $formattedDate = $targetDate->format('Y-m-d'); // ← ここが重要！
    } catch (\Exception $e) {
    return back()->withErrors(['target_date' => '日付が正しくありません']);
    }
       if($newClockIn || $newClockOut) {
        AttendanceEdit::create([
             'attendance_id' => null, // 新規なのでnull
            'user_id' => $user->id,
            'request_date' => $now,
            'target_date' => $targetDate->format('Y-m-d'),
            'new_clock_in' => $newClockIn ? Carbon::parse($targetDate->format('Y-m-d') . ' ' .  $newClockIn) : null,
            'new_clock_out' => $newClockOut ? Carbon::parse($targetDate->format('Y-m-d') . ' ' .  $newClockOut) : null,
            'reason' => $reason,
            'edited_by_admin' => true,
        ]);

        $attendance = Attendance::firstOrNew([
            'user_id' => $user->id,
            'date' => $targetDate->format('Y-m-d'),
        ]);
        // 出勤・退勤データを直接更新
        if (!is_null($newClockIn)) {
            $attendance->clock_in = Carbon::parse($targetDate->format('Y-m-d') . ' ' .    $newClockIn);
        }
        if (!is_null($newClockOut)) {
            $attendance->clock_out = Carbon::parse($targetDate->format('Y-m-d') . ' ' . $newClockOut);
        }
        $attendance->save();

       }
       $breaks = $request->input('breaks', []);

       foreach($breaks as $break) {
        $newIn = trim($break['clock_in'] ?? ' ') ?: null;
        $newOut = trim($break['clock_out'] ?? ' ') ?: null;

        // if($newIn || $newOut) {
        //  if ($breakId === null) {
        // 両方入力されていれば新規登録
                if ($newIn !== null || $newOut !== null) {
                BreakTimeEdit::create([
                    'break_time_id' => null,
                    'user_id' => $user->id,
                    'request_date' => $now,
                    'target_date' => $targetDate->format('Y-m-d'),
                    'new_clock_in' => $newIn ? Carbon::parse($targetDate->format('Y-m-d') . ' ' .  $newIn) : null,
                    'new_clock_out' => $newOut ? Carbon::parse($targetDate->format('Y-m-d') . ' ' .   $newOut) : null,
                    'reason' => $reason,
                    'edited_by_admin' => true,
                ]);
                // ② BreakTime作成（本データ）
                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'user_id' => $user->id,
                    'clock_in' => Carbon::parse($targetDate->format('Y-m-d') . ' ' . $newIn),
                    'clock_out' => Carbon::parse($targetDate->format('Y-m-d') . ' ' . $newOut),
                ]);


                }
            // }
        }
            return redirect()->route('admin.stamp_correction_request.list');
    }
       
    public function approvePage($attendance_correct_request)
    {
        $edit = AttendanceEdit::findOrFail($attendance_correct_request);
        $userId = $edit->user_id;
        $targetDate = $edit->target_date;

        $breakEdits = BreakTimeEdit::where('user_id', $userId)
         ->where('target_date', $targetDate)
         ->get();

        $data = $this->getAttendanceDetailData($userId, $targetDate);
        $data['edit'] = $edit;
        $data['break_edits'] = $breakEdits;

        return view('admin.approve',$data);
    }

    public function approveOnlyBreak(Request $request)
    {
        $userId = $request->query('user_id');
        $targetDate = $request->query('date');

        if (!$userId || !$targetDate) {
            abort(404, '必要なパラメータがありません。');
        }
        $edit = BreakTimeEdit::where('user_id', $userId)
        ->where('target_date', $targetDate)
        ->latest()
        ->first();

        $data = $this->getAttendanceDetailData($userId, $targetDate);

        $data['edit'] = $edit;

        return view('admin.approve', $data);
    }

    public function approveAttendanceEdit($id)
    {
    // $data['edit'] = $edit;
        $edit = AttendanceEdit::findOrFail($id);
        if ($edit->approved_at) {
        return redirect()->back()->with('message', 'すでに承認済みです。');
        }
        $edit->approved_at = now();
        $edit->save();

        if(is_null($edit->attendance_id)) {
        
        Attendance::create([
            'user_id' => $edit->user_id,
            'date' => $edit->target_date,
            'clock_in' => $edit->new_clock_in,
            'clock_out' => $edit->new_clock_out,
        ]);
        }else {
            $attendance = $edit->attendance;

            if($attendance) {
            $attendance->clock_in = $edit->new_clock_in ?? $attendance->clock_in;
            $attendance->clock_out = $edit->new_clock_out ?? $attendance->clock_out;
            $attendance->date = $edit->target_date ?? $attendance->date;
            $attendance->save();
            }
        }
         // ✅ 追加処理: 対応する休憩の申請も承認する
    $breakEdits = BreakTimeEdit::where('user_id', $edit->user_id)
        ->where('target_date', $edit->target_date)
        ->distinct()
        ->whereNull('approved_at') // 未承認のものだけ
        ->get();

    foreach ($breakEdits as $bedit) {
        // 削除申請
        if ($bedit->break_time_id && is_null($bedit->new_clock_in) && is_null($bedit->new_clock_out)) {
            $break = $bedit->breakTime;
            if ($break) $break->delete();
        }
        // 新規追加
         elseif (is_null($bedit->break_time_id)) {
            $attendance = Attendance::firstOrCreate(
                ['user_id' => $bedit->user_id, 'date' => $bedit->target_date],
                ['clock_in' => null, 'clock_out' => null]
            );
            BreakTime::create([
                'user_id' => $bedit->user_id,
                'attendance_id' => $attendance->id,
                'clock_in' => $bedit->new_clock_in,
                'clock_out' => $bedit->new_clock_out,
            ]);
        }
        // 修正
        else {
            $break = $bedit->breakTime;
            if ($break) {
                $break->clock_in = $bedit->new_clock_in ?? $break->clock_in;
                $break->clock_out = $bedit->new_clock_out ?? $break->clock_out;
                $break->save();
            }
        }
            $bedit->approved_at = now();
            $bedit->save();
    }
            // return redirect()->back()->with('message', '承認が完了しました。');
            return redirect()->route('admin.approvePage', ['attendance_correct_request' => $edit->id])
                 ->with('message', '承認が完了しました。');


    } 
        public function approveBreakEdit($id)

    {
        // \Log::info('🔥 Break 承認処理に入りました', ['id' => $id]);

        // \Log::info("break_time_id", ['value' => $edit->break_time_id]);
        // \Log::info("new_clock_in", ['value' => $edit->new_clock_in]);
        // \Log::info("new_clock_out", ['value' => $edit->new_clock_out]);
         $edit = BreakTimeEdit::findOrFail($id);
        // 同一ユーザーかつ同一日の未承認の休憩申請を取得
        $edits = BreakTimeEdit::where('user_id', $edit->user_id)
        ->where('target_date', $edit->target_date)
        ->whereNull('approved_at')
        ->get();

        foreach($edits as $edit) {
            if ($edit->break_time_id && is_null($edit->new_clock_in) && is_null($edit->new_clock_out)) {
             $break = $edit->breakTime;
            if ($break) {
                    $break->delete();
                }
             }elseif (is_null($edit->break_time_id)) {
        if(!is_null($edit->new_clock_in) || !is_null($edit->new_clock_out)) {
        
        // 新規作成  
            $attendance = Attendance::firstOrCreate(
                ['user_id' => $edit->user_id, 'date' => $edit->target_date],
                ['clock_in' => null, 'clock_out' => null]
            );
            BreakTime::create([
                'user_id' => $edit->user_id,
                'attendance_id' => $attendance->id,
               'clock_in' => $edit->new_clock_in,
                'clock_out' => $edit->new_clock_out,
                ]);
            }
        }else {
                $break = $edit->breakTime;
                 if ($break) {
                    $break->clock_in = $edit->new_clock_in ?? $break->clock_in;
                    $break->clock_out = $edit->new_clock_out ?? $break->clock_out;
                    $break->save();
                }
            }
    
        $edit->approved_at = now();
        $edit->save();
        }
         return redirect()->back()->with('message', '出勤データの承認が完了しました。');
    }
}