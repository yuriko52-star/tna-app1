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
        dd([
        'admin' => Auth::guard('admin')->user(),
        'web' => Auth::guard('web')->user(),
        'default' => Auth::user(),
    ]);

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
        ->where('edited_by_admin', false);

    $queryBreak = BreakTimeEdit::with(['user', 'breakTime', 'attendance'])
        ->where(function ($query) {
            $query->whereNotNull('new_clock_in')
                  ->orWhereNotNull('new_clock_out');
        })
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

    // 一般ユーザー用の処理
        /*$attendanceEdits = AttendanceEdit::with(['user'])
            ->where('user_id', $user->id)
            ->whereNull('approved_at')
            ->where('edited_by_admin',false)
            ->get()
            ->groupBy('target_date');

        $breakEdits = BreakTimeEdit::with(['user','breakTime', 'attendance'])
            ->where('user_id', $user->id)
            ->whereNull('approved_at')
            ->where('edited_by_admin',false)
            ->get()
            ->groupBy('target_date');
            */

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
        ->where(function ($query) {
            $query->whereNotNull('new_clock_in')
                  ->orWhereNotNull('new_clock_out');
        })
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
        /*$attendanceEdits = AttendanceEdit::with(['user','attendance'])
        ->whereNull('approved_at')
        ->where('edited_by_admin',false)
        ->get()
        ->groupBy('target_date');
       
        
        $breakEdits = BreakTimeEdit::with(['user','breakTime', 'attendance'])
            ->where(function ($query) {
                $query->whereNotNull('new_clock_in')
                    ->orWhereNotNull('new_clock_out');
    })
        ->whereNull('approved_at')
        ->where('edited_by_admin',false)
        ->get()
        ->groupBy('target_date');
        
        if($tab === 'waiting') {
            $attendanceEdits->whereNull('approved_at');
            $breakEdits->whereNull('approved_at');
         } elseif ($tab === 'approved') {
            $attendanceEdits ->whereNotNull('approved_at');
            $breakEdits->whereNotNull('approved_at');
         }
        
*/
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
            $mergedData[$date]['break_time_edits'] = $edits->values();
        }

        $mergedData = collect($mergedData)->sortBy('target_date')->values();

        return view('admin.edit', ['datas' => $mergedData,
        'tab' => $tab,
    ]);
    }
}


