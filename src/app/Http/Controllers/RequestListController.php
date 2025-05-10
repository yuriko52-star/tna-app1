<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceEdit;
use App\Models\BreakTimeEdit;
use Carbon\Carbon;

class RequestListController extends Controller
{
    public function index()
    {
        $admin = Auth::guard('admin')->user();
        $user = Auth::guard('web')->user();
        /*dd([
        'admin' => Auth::guard('admin')->user(),
        'web' => Auth::guard('web')->user(),
        'default' => Auth::user(),
        ]);
    */
        if ($admin) {
            return $this->adminRequestList($admin);
        } elseif ($user) {
            return $this->userRequestList($user);
        }

        return redirect('/login');
    }

    protected function userRequestList()
    {
         $user = Auth::guard('web')->user();
         $tab = request('tab','waiting');

         $queryAttendance = AttendanceEdit::with(['user', 'attendance'])
         ->where('user_id', $user->id)
        ->where('edited_by_admin', false);

        $queryBreak = BreakTimeEdit::with(['user', 'breakTime', 'attendance'])
        ->where('user_id', $user->id)
        ->where('edited_by_admin', false);

    if ($tab === 'waiting') {
        $queryAttendance->whereNull('approved_at');
        $queryBreak->whereNull('approved_at');
    } elseif ($tab === 'approved') {
        $queryAttendance->whereNotNull('approved_at');
        $queryBreak->whereNotNull('approved_at');
    }

    $attendanceEdits = $queryAttendance->get()->groupBy('target_date');
    $breakEdits = $queryBreak->get()->groupBy('target_date');

    
        $mergedData = [];

        foreach ($attendanceEdits as $date => $edits) {
            $mergedData[$date] = [
                'user' => $edits->first()->user,
                'target_date' => $date,
                'attendance_edits' => $edits,
                'break_time_edits' => collect(),
                'request_date' => $edits->first()->request_date,
                'reason' => $edits->first()->reason,
            ];
        }

        foreach ($breakEdits as $date => $edits) {
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

        $mergedData = collect($mergedData)->sortBy('target_date')->values();

        return view('attendance.edit', ['datas' => $mergedData,
        'tab' => $tab,
        ]);
    }

    protected function adminRequestList()
    {
        $admin = Auth::guard('admin')->user();
        $tab = request('tab','waiting');

        $queryAttendance = AttendanceEdit::with(['user', 'attendance'])
        ->where('edited_by_admin', false);

        $queryBreak = BreakTimeEdit::with(['user', 'breakTime', 'attendance'])
       ->where('edited_by_admin', false);

    if ($tab === 'waiting') {
        $queryAttendance->whereNull('approved_at');
        $queryBreak->whereNull('approved_at');
    } elseif ($tab === 'approved') {
        $queryAttendance->whereNotNull('approved_at');
        $queryBreak->whereNotNull('approved_at');
    }
     $attendanceEdits = $queryAttendance->get();
    
    $breakEdits = $queryBreak->get();
    
    $groupedAttendance = $attendanceEdits->groupBy('target_date');
    $groupedBreaks = $breakEdits->groupBy('target_date');
    
    $allDates = $groupedAttendance->keys()->merge($groupedBreaks->keys())->unique();
    $mergedData = [];

        foreach ($allDates as $date ) {
            $attendanceGroup = $groupedAttendance->get($date, collect());
            $breakGroup = $groupedBreaks->get($date, collect());

        if ($tab === 'approved') {
                $hasApprovedAttendance = $attendanceGroup->contains(function ($edit) {
                return !is_null($edit->approved_at);
            });

            $hasApprovedBreak = $breakGroup->contains(function ($edit) {
                return !is_null($edit->approved_at);
            });

            if (!$hasApprovedAttendance && !$hasApprovedBreak) {
                continue;
            }
        }
        
        $user = $attendanceGroup->first()->user ?? $breakGroup->first()->user;
       
        $requestDate = $attendanceGroup->first()->request_date ?? $breakGroup->first()->request_date;
        $reason = $attendanceGroup->first()->reason ?? $breakGroup->first()->reason;

            $mergedData[$date] = [
                
                'user' => $user,
                'target_date' => $date,
                'attendance_edits' => $attendanceGroup,
                'break_time_edits' => $breakGroup,
                'request_date' => $requestDate,
                'reason' => $reason,
                ];
        }

       $mergedData = collect($mergedData)->sortBy('target_date')->values();

        return view('admin.edit', ['datas' => $mergedData,
        'tab' => 'approved',
        ]);
    }
}


