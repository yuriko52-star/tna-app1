<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\AttendanceEdit;
use App\Models\BreakTime;
use App\Models\BreakTimeEdit;
use Carbon\Carbon;

class UserController extends AttendanceDetailController
{
    
    public function index() {
        $user = Auth::user();
        
        $today = now()->isoFormat('YYYY年M月D日(ddd)');
        $currentTime = now()->format('H:i');


        $attendance = Attendance::where('user_id',$user->id)->whereDate('date',now()->toDateString())->first();
        
        $status = $this->getCurrentStatus($user);
       
        return view('attendance.record',compact('status','today','currentTime'));
    }
    private function getCurrentStatus($user)
    {
        
        $attendance = Attendance::where('user_id',$user->id)->WhereDate('date',now()->toDateString())->latest()->first();
        if(!$attendance) {
            return '勤務外';
        }
        if($attendance->clock_out) {
            return '退勤済';
        } 
        
        $breakTime = BreakTime::where('attendance_id',$attendance->id)->latest('clock_in')->first();
        if($breakTime && $breakTime->clock_in && !$breakTime->clock_out) {
            return '休憩中';
        } 
         if ($attendance->clock_in) {
             return '出勤中';
         }
        
        return '勤務外';

    }
    public function showList(Request $request) {
        $user = Auth::user();
        
        $monthParam = $request->query('month');
        
        $targetMonth = $monthParam ? Carbon::parse($monthParam . '-01'): now();

        $thisMonth = $targetMonth->format('Y/m');
        
        $previousMonth = $targetMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $targetMonth->copy()->addMonth()->format('Y-m');

        $startOfMonth = $targetMonth->copy()->startOfMonth();
        $endOfMonth = $targetMonth->copy()->endOfMonth();
        
        $dates = [];
        $currentDate = $startOfMonth->copy();
        while ($currentDate <= $endOfMonth) {
            $dates[] = $currentDate->copy();
            $currentDate->addDay();
        }
       
        $attendances = Attendance::with('breakTimes')
        ->where('user_id',$user->id)
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

            $attendancePending = optional($data)->has_pending_edit ?? false;

            $breakPending = false;
            if($data) {
                $breakPending = BreakTimeEdit::where('user_id',$user->id)
                ->where('target_date',$dateKey)
                ->whereNull('approved_at')
                ->where('edited_by_admin',0)
                ->exists();
            }
            $hasPendingEdit = $attendancePending || $breakPending;
            
            $attendanceData[] = [
                
                 'id'=> optional($data)->id, 
                'raw_date' => $date->format('Y-m-d'),
                'date' => $date->format('m/d') . '(' . $weekMap[$date->format('D')] . ')' ,
                'clockIn' => $clockIn ? $clockIn->format('H:i') : '',
                'clockOut' => $clockOut ? $clockOut->format('H:i') : '',
                'breakTime' => ($clockIn && $clockOut) ? $this->formatMinutes($totalBreakMinutes) : '',
                'workingTime' => ($clockIn && $clockOut) ?$this->formatMinutes($workingMinutes) : '',
                'has_pending_edit' => $hasPendingEdit,
            ];
        }
        
        return view ('attendance.index' ,compact('thisMonth','previousMonth','nextMonth','attendanceData'));
    }

    private function formatMinutes($minutes) {
        $hours = floor($minutes/ 60);
        $mins = $minutes % 60;
        return sprintf('%d:%02d', $hours, $mins);
    }
    public function detail($id) {
        $attendance = Attendance::with('breakTimes')->findOrFail($id);

        if($attendance->user_id !== Auth::id()) {
            abort(403,'この勤怠情報にアクセスする権限がありません。');
        }

        $date = Carbon::parse($attendance->date);
        $year = $date->format('Y年');
        $monthDay = $date->format('n月j日');
        return view('attendance.detail',compact('attendance','year','monthDay'));

    }
    public function detailByDate($date)
    {
        $user = Auth::user();
        $attendance = Attendance::with('breakTimes')
        ->where('user_id', $user->id)
        ->whereDate('date', $date)
        ->first();

    
        if (!$attendance) {
            $attendance = new Attendance([
            'user_id' => $user->id,
            'date' => $date,
            'clock_in' => null,
            'clock_out' => null,
            ]);
            $attendance->breakTimes = collect(); 
        }

        
        $carbonDate = \Carbon\Carbon::parse($date);
        $year = $carbonDate->format('Y年');
        $monthDay = $carbonDate->format('n月j日');

        return view('attendance.detail', compact('attendance', 'year', 'monthDay'));
    }

    public function editDetail(Request $request ,$date )
    {
        $user = Auth::user();
        $attendanceEdit = AttendanceEdit::where('user_id',$user->id)
        ->where('target_date',$date)
        ->where('edited_by_admin',0)
        ->orderByDesc('request_date')
        ->first();

        $breakEdits = BreakTimeEdit::where('user_id', $user->id)
        ->where('target_date', $date)
        ->where('edited_by_admin',0)
        ->orderByDesc('request_date')
        ->get();

        $data = $this->getAttendanceDetailData($user->id, $date);
        return view('attendance.approve',array_merge($data, [
            'attendanceEdit' => $attendanceEdit,
            'breakEdits' => $breakEdits,
        ]));
    }
        
 }