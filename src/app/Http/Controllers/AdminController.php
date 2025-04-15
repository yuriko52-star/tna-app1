<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceEdit;
use App\Models\BreakTimeEdit;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.list');
    }
    public function staffList()
    {
        return view ('admin.staff-list');
    }
    
        /*public function requestList()
{
    // 管理者なので、全ユーザーの申請を見る
    $attendanceEdits = AttendanceEdit::with(['user', 'attendance'])
        ->get()
        ->groupBy('target_date');

    $breakEdits = BreakTimeEdit::with(['user', 'breakTime', 'attendance'])
        ->get()
        ->groupBy('target_date');

    $mergedData = [];

    foreach($attendanceEdits as $date => $edits) {
        $mergedData[$date] = [
            'user' => $edits->first()->user,
            'target_date' => $date,
            'attendance_edits' => $edits,
            'break_time_edits' => collect(),
            'request_date' => $edits->first()->request_date,
            'reason' => $edits->first()->reason,
        ];
    }

    foreach($breakEdits as $date => $edits) {
        if (!isset($mergedData[$date])) {
            $mergedData[$date] = [
                'user' => $edits->first()->user,
                'target_date' => $date,
                'attendance_edits' => collect(),
                'break_time_edits' => collect(),
                'request_date' => $edits->first()->request_date,
                'reason' => $edits->first()->reason,
            ];
        }
        $mergedData[$date]['break_time_edits'] = $edits->sortBy('start_time')->values();
    }

    $mergedData = collect($mergedData)->sortBy('request_date')->values();

    return view('attendance.edit', ['datas' => $mergedData]);


    
    }
    */
}
