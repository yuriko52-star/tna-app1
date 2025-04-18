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

class AdminController extends Controller
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

     public function update(AttendanceRequest $request , $id) 
    {
        $admin = Auth::guard('admin')->user();
    
        

       $attendance = Attendance::with('breakTimes')->findOrFail($id);
       $user = $attendance->user;
       
         
         $newClockIn = $request->input('clock_in');
         if ($newClockIn === '') {
            $newClockIn = null;
            }
         $newClockOut = $request->input('clock_out');
         if ($newClockOut === '') {
            $newClockOut = null;
        }
        
        $defaultClockIn = optional($attendance)->clock_in;
         $defaultClockOut = optional($attendance)->clock_out;
        //  下のコードを追加したよ
        //  $defaultTargetDate = optional($attendance)->date;
        $year = $request->input('target_year');
        $month = $request->input('target_month');
        $day = $request->input('target_day');

    try {
        $targetDate = Carbon::createFromDate($year, $month, $day);
        $formattedDate = $targetDate->format('Y-m-d'); // ← ここが重要！
    } catch (\Exception $e) {
    return back()->withErrors(['target_date' => '日付が正しくありません']);
    }

         /*$targetDateInput = $request->input('target_date');
         $targetDate = $targetDateInput ? Carbon::parse($targetDateInput) : Carbon::parse($attendance->date);
         */
          /*$year = Carbon::parse($attendance->date)->format('Y年');
         $monthDay = Carbon::parse($attendance->date)->format('n月j日');
         */
        //  $targetDate = $attendance->date;
          $now = now();
         $reason = $request->input('reason');
        // 出勤・退勤の変更判定（時間のみ比較）
        $isClockInChanged = $newClockIn !== null && $defaultClockIn && Carbon::parse($defaultClockIn)->format('H:i') !== $newClockIn;
        $isClockOutChanged = $newClockOut !== null && $defaultClockOut && Carbon::parse($defaultClockOut)->format('H:i') !== $newClockOut;
        $isClockInDeleted = $newClockIn === null && $defaultClockIn !== null;
        $isClockOutDeleted = $newClockOut === null && $defaultClockOut !== null;

        if ($isClockInChanged || $isClockOutChanged || $isClockInDeleted || $isClockOutDeleted) {
                AttendanceEdit::create([
                'attendance_id' => $attendance->id,
                'user_id' => $user->id,
                'request_date' => $now,
                'target_date' => $targetDate->format('Y-m-d'),
                'new_clock_in' => $isClockInChanged ? Carbon::parse($targetDate->format('Y-m-d') . ' ' . $newClockIn) : null,
                'new_clock_out' => $isClockOutChanged ? Carbon::parse($targetDate->format('Y-m-d') . ' ' . $newClockOut) : null,

                
                'reason' => $reason,
            ]);
         }
         // 休憩の修正申請
        $breaks = $request->input('breaks', []);
        //  $breakTimeMap = $attendance->breakTimes->keyBy('id'); // ← IDで紐付け！消してみた。

         foreach($breaks as $break)
       {
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
            ]);
        }
        continue;
    }
    
        //  既存の休憩：修正 or 削除のチェック
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
                        ]);
                    }
                 }
                
               return redirect()->route('admin.stamp_correction_request.list');

    }
    public function store(AttendanceRequest $request)
    {
       $user = Auth::user();
       $targetDate = $request->input('date');
       $now = now();
       $reason = $request->input('reason');
       $newClockIn = $request->input('clock_in');
       $newClockOut = $request->input('clock_out');
       
       if($newClockIn || $newClockOut) {
        AttendanceEdit::create([
             'attendance_id' => null, // 新規なのでnull
            'user_id' => $user->id,
            'request_date' => $now,
            'target_date' => $targetDate,
            'new_clock_in' => $newClockIn ? Carbon::parse("$targetDate $newClockIn") : null,
            'new_clock_out' => $newClockOut ? Carbon::parse("$targetDate $newClockOut") : null,
            'reason' => $reason,
        ]);
       }
       $breaks = $request->input('breaks', []);

       foreach($breaks as $break) {
        $newIn = $break['clock_in'] ?? null;
        $newOut = $break['clock_out'] ?? null;

        if($newIn || $newOut) {
            BreakTimeEdit::create([
                'break_time_id' => null,
                'user_id' => $user->id,
                'request_date' => $now,
                'target_date' => $targetDate,
                'new_clock_in' => $newIn ? Carbon::parse("$targetDate $newIn") : null,
                'new_clock_out' => $newOut ? Carbon::parse("$targetDate $newOut") : null,
                'reason' => $reason,
            ]);
        }
       }
       return redirect()->route('user.stamp_correction_request.list');
    }
       
}   

     

