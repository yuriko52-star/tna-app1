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
        ->first();

    $workclockIn = $attendanceEdit && $attendanceEdit->new_clock_in !== null
        ? $attendanceEdit->new_clock_in
        : ($attendance->clock_in ?? null);

    $workclockOut = $attendanceEdit && $attendanceEdit->new_clock_out !== null
        ? $attendanceEdit->new_clock_out
        : ($attendance->clock_out ?? null);

    $breakTimes = BreakTime::where('attendance_id', $attendance->id ?? null)->get();
    $breakEdits = BreakTimeEdit::where('user_id', $userId)
        ->where('target_date', $targetDate)
        ->get();

    $mergedBreaks = [];

    foreach ($breakEdits as $edit) {
        if ($edit->new_clock_in === null && $edit->new_clock_out === null) {
            continue;
        }

        if ($edit->break_time_id) {
            $original = $breakTimes->firstWhere('id', $edit->break_time_id);
            $clockIn = $edit->new_clock_in ?? $original->clock_in;
            $clockOut = $edit->new_clock_out ?? $original->clock_out;
        } else {
            $clockIn = $edit->new_clock_in;
            $clockOut = $edit->new_clock_out;
        }

        $mergedBreaks[] = [
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
        ];
    }

    foreach ($breakTimes as $break) {
        $alreadyHandled = $breakEdits->contains('break_time_id', $break->id);
        if (!$alreadyHandled) {
            $mergedBreaks[] = [
                'clock_in' => $break->clock_in,
                'clock_out' => $break->clock_out,
            ];
        }
    }

    $mergedBreaks = collect($mergedBreaks)->unique(function ($item) {
        return $item['clock_in'] . '-' . $item['clock_out'];
    })->sortBy('clock_in')->values();

    return [
        'user' => $user,
        'year' => Carbon::parse($targetDate)->format('Y年'),
        'monthDay' => Carbon::parse($targetDate)->format('m月d日'),
        'workclockIn' => $workclockIn,
        'workclockOut' => $workclockOut,
        'mergedBreaks' => $mergedBreaks,
        'reason' => $attendanceEdit->reason ?? $breakEdits->first()->reason ?? '',
    ];
}

}
