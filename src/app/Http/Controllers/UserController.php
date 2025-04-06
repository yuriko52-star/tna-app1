<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class UserController extends Controller
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
        // 直近の勤怠データを取得（データベースのカラムに応じて変更）
        $attendance = Attendance::where('user_id',$user->id)->WhereDate('date',now()->toDateString())->latest()->first();
        if(!$attendance) {
            return '勤務外';
        }
        // 勤怠ステータスの判定

        
        if($attendance->clock_out) {
            return '退勤済';
        } 
        
        $breakTime = BreakTime::where('attendance_id',$attendance->id)->latest()->first();
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
                
                'id'=> optional($data)->id,
                'date' => $date->format('m/d') . '(' . $weekMap[$date->format('D')] . ')' ,
                'clockIn' => $clockIn ? $clockIn->format('H:i') : '',
                'clockOut' => $clockOut ? $clockOut->format('H:i') : '',
                'breakTime' => $this->formatMinutes($totalBreakMinutes),
                'workingTime' => $this->formatMinutes($workingMinutes),
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
        return view('attendance.detail',compact('attendance'));

    }
}
