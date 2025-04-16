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

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.list');
    }
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
        //  ->where('user_id',$user->id)
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
    public function detailByDate($date)
{
    $user = Auth::user();

    // 該当する勤怠データを取得（なければ null）
    $attendance = Attendance::with('breakTimes')
        ->where('user_id', $user->id)
        ->whereDate('date', $date)
        ->first();

    // データがない場合は空の Attendance オブジェクトを作成して渡す（修正申請の入力用）
    if (!$attendance) {
        $attendance = new Attendance([
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

    return view('attendance.detail', compact('attendance', 'year', 'monthDay'));
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
