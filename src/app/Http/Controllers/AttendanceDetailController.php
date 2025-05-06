<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceEdit;
use App\Models\BreakTime;
use App\Models\BreakTimeEdit;
use App\Models\User;

use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceDetailController extends Controller
{
    protected function getAttendanceDetailData($userId, $targetDate)
{
    $user = User::findOrFail($userId);
    $targetDate = Carbon::parse($targetDate)->format('Y-m-d');

    $attendance = Attendance::where('user_id', $userId)
        ->where('date', $targetDate)
        ->first();

    $attendanceEdit = AttendanceEdit::where('user_id', $userId)
        ->where('target_date', $targetDate)
        ->where('edited_by_admin',0)
        ->orderByDesc('request_date')// ★追加：新しい申請が優先
        ->first();

    $workclockIn = $attendanceEdit && $attendanceEdit->new_clock_in !== null
        ? $attendanceEdit->new_clock_in
        : ($attendance->clock_in ?? null);

    $workclockOut = $attendanceEdit && $attendanceEdit->new_clock_out !== null
        ? $attendanceEdit->new_clock_out
        : ($attendance->clock_out ?? null);

    $breakTimes = BreakTime::where('attendance_id', $attendance->id ?? null)
        ->whereNull('deleted_at')
        ->get();
    $breakEdits = BreakTimeEdit::where('user_id', $userId)
        ->where('target_date', $targetDate)
        ->where('edited_by_admin',0)
        ->orderByDesc('request_date')// ★最新順に取得
        ->get();

     $mergedBreaks = [];

    // 1. 修正対象IDだけ抜き出す（新規追加除外）
    $handledBreakIds = $breakEdits
    ->filter(function ($edit) {
        return $edit->break_time_id !== null;
    })
    ->pluck('break_time_id')
    ->all();
   
     $deletedBreakIds = $breakEdits
    ->filter(function ($edit) {
        return $edit->new_clock_in === null && $edit->new_clock_out === null  && $edit->break_time_id !== null;
    })
    ->pluck('break_time_id')
    // ->filter()
    ->all();


    foreach ($breakEdits as $edit) {
        if ($edit->new_clock_in === null && $edit->new_clock_out === null) {
            continue;
        }
 if (is_null($edit->break_time_id)) {
        // break_time_idがnullなら、新規追加
        // でも、もしattendance_idに紐づくbreak_timesに同じ休憩があれば無視！
        $alreadyExists = $breakTimes->contains(function ($break) use ($edit) {
            return
                Carbon::parse($break->clock_in)->format('H:i') === Carbon::parse($edit->new_clock_in)->format('H:i') &&
                Carbon::parse($break->clock_out)->format('H:i') === Carbon::parse($edit->new_clock_out)->format('H:i');
        });

        if ($alreadyExists) {
            continue; // すでにbreak_timesに登録されてるからskip！
        }
    }
        if ($edit->break_time_id) {
            $original = $breakTimes->firstWhere('id', $edit->break_time_id);
            $clockIn = $edit->new_clock_in ?? $original->clock_in;
            $clockOut = $edit->new_clock_out ?? $original->clock_out;
        } else {
            $clockIn = $edit->new_clock_in;
            $clockOut = $edit->new_clock_out;
        }
// clock_inとclock_outをキーにして管理
    $key = ($clockIn ?? '') . '-' . ($clockOut ?? '');
    $mergedBreaks[$key] = [
        'clock_in' => $clockIn,
        'clock_out' => $clockOut,
    ];
}
     
    
    // 次に、編集対象になっていない元の休憩も反映
        foreach ($breakTimes as $break) {
            if (in_array($break->id, $handledBreakIds) || in_array($break->id, $deletedBreakIds))  {
            continue; // 編集済みならスキップ
        }
         $key = ($break->clock_in ? \Carbon\Carbon::parse($break->clock_in)->format('H:i') : '') . '-' . ($break->clock_out ? \Carbon\Carbon::parse($break->clock_out)->format('H:i') : '');
        $mergedBreaks[$key] = [
            'clock_in' => $break->clock_in,
            'clock_out' => $break->clock_out,
        ];
    }
    // 最後にsortしてvalues取り出し
        $mergedBreaks = collect($mergedBreaks)->sortBy('clock_in')->values();
        // dd($mergedBreaks);
    /*foreach ($breakTimes as $break) {
        $alreadyHandled = $breakEdits->contains('break_time_id', $break->id);
        if (!$alreadyHandled) {
            $mergedBreaks[] = [
                'clock_in' => $break->clock_in,
                'clock_out' => $break->clock_out,
            ];
        }
    }*/

    /*$mergedBreaks = collect($mergedBreaks)->unique(function ($item) {
        return $item['clock_in'] . '-' . $item['clock_out'];
    })->sortBy('clock_in')->values();
    */

    return [
        'user' => $user,
        'year' => Carbon::parse($targetDate)->format('Y年'),
        'monthDay' => Carbon::parse($targetDate)->format('n月j日'),
        'workclockIn' => $workclockIn,
        'workclockOut' => $workclockOut,
        'mergedBreaks' => $mergedBreaks,
        'reason' => $attendanceEdit->reason ?? $breakEdits->first()->reason ?? '',
    ];
}

}
