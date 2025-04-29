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
            // 表示用データに整形
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
        $year = $date->format('Y');
        $monthDay = $date->format('n月j日');
        return view('attendance.detail',compact('attendance','year','monthDay'));

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
        ->orderByDesc('request_date')// ★最新順に取得
        ->get();

        $data = $this->getAttendanceDetailData($user->id, $date);
        return view('attendance.approve',array_merge($data, [
            'attendanceEdit' => $attendanceEdit,
            'breakEdits' => $breakEdits,
        ]));
        /*$targetDate = Carbon::parse($date)->format('Y-m-d');
        $userId =  Auth::id();
    // 出勤データと修正データを取得
        $attendance = Attendance::where('user_id',$userId)
        ->where('date' ,$targetDate)
        ->first();
        $attendanceEdit = AttendanceEdit::where('user_id',$userId)
        ->where('target_date',$targetDate)
        ->first();
    // 勤務時間（修正があればそれを優先）
        $workclockIn = $attendanceEdit && $attendanceEdit->new_clock_in !== null ? $attendanceEdit->new_clock_in : ($attendance->clock_in ?? null);
        // ちょっと個別のコードと違うので個別コード優先
        $workclockOut = $attendanceEdit && $attendanceEdit->new_clock_out !==null ? $attendanceEdit->new_clock_out : ($attendance->clock_out ?? null);
         // 休憩データ取得（元データ＋修正データ）
        $breakTimes = BreakTime::where('attendance_id', $attendance->id ?? null)->get();
        
        $breakEdits = BreakTimeEdit::where('user_id',$userId)
        ->where('target_date', $targetDate)
        ->get();

    // 休憩の表示用データ合成
        $mergedBreaks = [];

        foreach($breakEdits as $edit) {
            // 削除申請（両方 null）の場合はスキップ
            if($edit->new_clock_in === null && $edit->new_clock_out === null) {
                continue;
            }

           // 元の休憩とマッチしてる場合
           if($edit->break_time_id) {
            $original = $breakTimes->firstWhere('id', $edit->break_time_id);

            $clockIn = $edit->new_clock_in !== null ? $edit->new_clock_in : $original->clock_in;

            $clockOut = $edit->new_clock_out !== null ? $edit->new_clock_out : $original->clock_out;
           } else {
            $clockIn = $edit->new_clock_in;
            $clockOut = $edit->new_clock_out;
           }

           $mergedBreaks[] = [
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
           ];
        }
        //  元データで修正されていない休憩を表示に追加（削除されたものを除く）

        foreach ($breakTimes as $break) {
            $alreadyHandled = $breakEdits->contains('break_time_id',$break->id);
            if(!$alreadyHandled) {
                $mergedBreaks [] = [
                    'clock_in' => $break->clock_in,
                    'clock_out' => $break->clock_out,
                    ];
            }
        }
        $mergedBreaks = collect($mergedBreaks)
        ->sortBy('clock_in')
        ->values();
        // 名前、日付、理由など（編集申請がある場合）ちょっと保留
        // // フォーマット用
        $date = $targetDate;
        $year = Carbon::parse($date)->format('Y年');
        $monthDay = Carbon::parse($date)->format('m月d日');
        $reason = $attendanceEdit->reason ?? $breakEdits->first()->reason ?? '';
        
        return view('attendance.approve',compact('user','year','monthDay','workclockIn','workclockOut','mergedBreaks','reason'));
        */
    }
        
 }