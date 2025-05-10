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
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends AttendanceDetailController
{
    
    public function staffList()
    {
        $user = Auth::guard('web')->user();
        $users = User::where('role', 'user')
        ->select(['id','name','email'])->get();

        return view ('admin.staff-list',compact('users'));
    }
    public function showList(Request $request,$id) {
        $admin = Auth::guard('admin')->user();
        $user = User::findOrFail($id);
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
        
        $user = User::findOrFail($id);
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
   
    $year = $carbonDate->format('Y');
    $monthDay = $carbonDate->format('n月j日');
    
    $raw_date = $carbonDate->format('Y-m-d');

    return view('admin.detail', compact('attendance', 'year', 'monthDay','user','raw_date'));
}

    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();
       
        $dayParam = $request->query('day');
        
        $targetDay = $dayParam ? Carbon::parse($dayParam . '-01'): now();
        $today = $targetDay->isoFormat('YYYY年M月D日');

        $thisDay = $targetDay->format('Y/m/d');
        
        $previousDay = $targetDay->copy()->subDay()->format('Y-m-d');
        $nextDay = $targetDay->copy()->addDay()->format('Y-m-d');
        
        $attendances = Attendance::with(['breakTimes','user'])
        ->WhereDate('date', $targetDay)
        ->get();
        $attendanceData = [];
         foreach ($attendances as $attendance) {
             $clockIn = $attendance->clock_in ? Carbon::parse($attendance->clock_in) : null;
            $clockOut = $attendance->clock_out ? Carbon::parse($attendance->clock_out) : null;
          
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

public function update(AttendanceRequest $request, $id)
{
   

    $admin = Auth::guard('admin')->user();
    $attendance = Attendance::with('breakTimes')->findOrFail($id);
    $user = $attendance->user;

    $now = now();
    $reason = $request->input('reason');

    $newClockIn = $request->input('clock_in') !== '' ? $request->input('clock_in') : null;
    $newClockOut = $request->input('clock_out') !== '' ? $request->input('clock_out') : null;

    $year = $request->input('target_year');
    $year = preg_replace('/[^0-9]/', '', $year);
    $monthDay = $request->input('target_month_day');
    if(preg_match('/(\d+)月(\d+)日/', $monthDay, $matches)) {
        $month = $matches[1];
        $day = $matches[2];
    }else {
         return back()->withErrors(['target_month_day' => '月日を正しく入力してください（例：4月26日）']); 
    }
    
    try {
        $targetDate = Carbon::createFromDate($year, $month, $day);
        $formattedDate = $targetDate->format('Y-m-d');
    } catch (\Exception $e) {
        return back()->withErrors(['target_date' => '日付が正しくありません']);
    }

    $defaultClockIn = optional($attendance)->clock_in;
    $defaultClockOut = optional($attendance)->clock_out;
    $originalDate = Carbon::parse($attendance->date)->format('Y-m-d');

    $isClockInChanged = $newClockIn !== null && ($defaultClockIn === null || Carbon::parse($defaultClockIn)->format('H:i') !== $newClockIn);
    $isClockOutChanged = $newClockOut !== null && ($defaultClockOut === null || Carbon::parse($defaultClockOut)->format('H:i') !== $newClockOut);
    $isClockInDeleted = $newClockIn === null && $defaultClockIn !== null;
    $isClockOutDeleted = $newClockOut === null && $defaultClockOut !== null;
    $isDateChanged = $formattedDate !== $originalDate;

   
    if ($isDateChanged) {
       
        Attendance::where('user_id', $user->id)
            ->where('date', $formattedDate)
            ->where('id', '!=', $attendance->id)
            ->delete();
     
        BreakTime::whereHas('attendance', function ($query) use ($user, $formattedDate, $attendance) {
            $query->where('user_id', $user->id)
                  ->where('date', $formattedDate)
                  ->where('id', '!=', $attendance->id);
        })->delete();
    }

    
    if ($isClockInChanged || $isClockOutChanged || $isClockInDeleted || $isClockOutDeleted || $isDateChanged) {
        AttendanceEdit::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'request_date' => $now,
            'target_date' => $formattedDate,
            'new_clock_in' => $isClockInChanged ? Carbon::parse($formattedDate . ' ' . $newClockIn) : null,
            'new_clock_out' => $isClockOutChanged ? Carbon::parse($formattedDate . ' ' . $newClockOut) : null,
            'reason' => $reason,
            'edited_by_admin' => true,
        ]);
    }

     
    $attendance->date = $formattedDate;
    $attendance->clock_in = $newClockIn ? Carbon::parse($formattedDate . ' ' . $newClockIn) : null;
    $attendance->clock_out = $newClockOut ? Carbon::parse($formattedDate . ' ' . $newClockOut) : null;
    $attendance->save();

    
    $breaks = $request->input('breaks', []);
   
    $attendance->breakTimes()->delete(); 
   
    foreach ($breaks as $break) {
        $newIn = trim($break['clock_in'] ?? '') ?: null;
        $newOut = trim($break['clock_out'] ?? '') ?: null;

        if ($newIn || $newOut) {
            
            BreakTimeEdit::create([
                'break_time_id' => null,
                'user_id' => $user->id,
                'request_date' => $now,
                'target_date' => $formattedDate,
                'new_clock_in' => $newIn ? Carbon::parse($formattedDate . ' ' . $newIn) : null,
                'new_clock_out' => $newOut ? Carbon::parse($formattedDate . ' ' . $newOut) : null,
                'reason' => $reason,
                'edited_by_admin' => true,
            ]);
             
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'user_id' => $user->id,
                'clock_in' => $newIn ? Carbon::parse($formattedDate . ' ' . $newIn) : null,
                'clock_out' => $newOut ? Carbon::parse($formattedDate . ' ' . $newOut) : null,
            ]);
        }
    }

    return redirect()->route('admin.attendance.staff',['id' => $attendance->user_id])
        ->with('message', '更新が完了しました。');
}
    public function store(AttendanceRequest $request,$id)
    {
         $admin = Auth::guard('admin')->user();
      
        $user = User::findOrFail($id);
    
       $now = now();
       $reason = $request->input('reason');
       $newClockIn = $request->input('clock_in');
       $newClockOut = $request->input('clock_out');
        $year = $request->input('target_year');
        $year = preg_replace('/[^0-9]/', '', $year);
        $monthDay = $request->input('target_month_day');

            if(preg_match('/(\d+)月(\d+)日/', $monthDay, $matches)) {
                $month = $matches[1];
                $day = $matches[2];
            }else {
         return back()->withErrors(['target_month_day' => '月日を正しく入力してください（例：4月26日）']); 
            }

        try {
        $targetDate = Carbon::createFromDate($year, $month, $day);
        
    } catch (\Exception $e) {
    return back()->withErrors(['target_date' => '日付が正しくありません']);
    }
       if($newClockIn || $newClockOut) {
        AttendanceEdit::create([
             'attendance_id' => null, 
            'user_id' => $user->id,
            'request_date' => $now,
            'target_date' => $targetDate->format('Y-m-d'),
            'new_clock_in' => $newClockIn ? Carbon::parse($targetDate->format('Y-m-d') . ' ' .  $newClockIn) : null,
            'new_clock_out' => $newClockOut ? Carbon::parse($targetDate->format('Y-m-d') . ' ' .  $newClockOut) : null,
            'reason' => $reason,
            'edited_by_admin' => true,
        ]);

        $attendance = Attendance::firstOrNew([
            'user_id' => $user->id,
            'date' => $targetDate->format('Y-m-d'),
        ]);
        
        if (!is_null($newClockIn)) {
            $attendance->clock_in = Carbon::parse($targetDate->format('Y-m-d') . ' ' .    $newClockIn);
        }
        if (!is_null($newClockOut)) {
            $attendance->clock_out = Carbon::parse($targetDate->format('Y-m-d') . ' ' . $newClockOut);
        }
        $attendance->save();

       }
       $breaks = $request->input('breaks', []);

       foreach($breaks as $break) {
        $newIn = trim($break['clock_in'] ?? ' ') ?: null;
        $newOut = trim($break['clock_out'] ?? ' ') ?: null;

        
                if ($newIn !== null || $newOut !== null) {
                BreakTimeEdit::create([
                    'break_time_id' => null,
                    'user_id' => $user->id,
                    'request_date' => $now,
                    'target_date' => $targetDate->format('Y-m-d'),
                    'new_clock_in' => $newIn ? Carbon::parse($targetDate->format('Y-m-d') . ' ' .  $newIn) : null,
                    'new_clock_out' => $newOut ? Carbon::parse($targetDate->format('Y-m-d') . ' ' .   $newOut) : null,
                    'reason' => $reason,
                    'edited_by_admin' => true,
                ]);
               
                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'user_id' => $user->id,
                    'clock_in' => Carbon::parse($targetDate->format('Y-m-d') . ' ' . $newIn),
                    'clock_out' => Carbon::parse($targetDate->format('Y-m-d') . ' ' . $newOut),
                ]);
            }
        }
            return redirect()->route('admin.attendance.staff',['id' => $attendance->user_id]);
    }
       
    public function approvePage($attendance_correct_request)
    {
        $edit = AttendanceEdit::findOrFail($attendance_correct_request);
        $userId = $edit->user_id;
        $targetDate = $edit->target_date;

        $breakEdits = BreakTimeEdit::where('user_id', $userId)
         ->where('target_date', $targetDate)
         ->get();

        $data = $this->getAttendanceDetailData($userId, $targetDate);
        $data['edit'] = $edit;
        $data['break_edits'] = $breakEdits;

        return view('admin.approve',$data);
    }

    public function approveOnlyBreak(Request $request)
    {
        $userId = $request->query('user_id');
        $targetDate = $request->query('date');

        if (!$userId || !$targetDate) {
            abort(404, '必要なパラメータがありません。');
        }
        $edit = BreakTimeEdit::where('user_id', $userId)
        ->where('target_date', $targetDate)
        ->latest()
        ->first();

        $data = $this->getAttendanceDetailData($userId, $targetDate);

        $data['edit'] = $edit;

        return view('admin.approve', $data);
    }

    public function approveAttendanceEdit($id)
    {
    
        $edit = AttendanceEdit::findOrFail($id);
        if ($edit->approved_at) {
        return redirect()->back()->with('message', 'すでに承認済みです。');
        }
        $edit->approved_at = now();
        $edit->save();

        if(is_null($edit->attendance_id)) {
        
        Attendance::create([
            'user_id' => $edit->user_id,
            'date' => $edit->target_date,
            'clock_in' => $edit->new_clock_in,
            'clock_out' => $edit->new_clock_out,
        ]);
        }else {
            $attendance = $edit->attendance;

            if($attendance) {
            $attendance->clock_in = $edit->new_clock_in ?? $attendance->clock_in;
            $attendance->clock_out = $edit->new_clock_out ?? $attendance->clock_out;
            $attendance->date = $edit->target_date ?? $attendance->date;
            $attendance->save();
            }
        }
         
    $breakEdits = BreakTimeEdit::where('user_id', $edit->user_id)
        ->where('target_date', $edit->target_date)
        ->where('edited_by_admin', 0)
        ->distinct()
        ->whereNull('approved_at') 
        ->get();

    foreach ($breakEdits as $bedit) {
       
        if ($bedit->break_time_id && is_null($bedit->new_clock_in) && is_null($bedit->new_clock_out)) {
            $break = $bedit->breakTime;
            if ($break) $break->delete();
        }
        
         elseif (is_null($bedit->break_time_id)) {
            $attendance = Attendance::firstOrCreate(
                ['user_id' => $bedit->user_id, 'date' => $bedit->target_date],
                ['clock_in' => null, 'clock_out' => null]
            );
            BreakTime::create([
                'user_id' => $bedit->user_id,
                'attendance_id' => $attendance->id,
                'clock_in' => $bedit->new_clock_in,
                'clock_out' => $bedit->new_clock_out,
            ]);
        }else {
            $break = $bedit->breakTime;
            if ($break) {
                $break->clock_in = $bedit->new_clock_in ?? $break->clock_in;
                $break->clock_out = $bedit->new_clock_out ?? $break->clock_out;
                $break->save();
            }
        }
            $bedit->approved_at = now();
            $bedit->save();
    }
        return redirect()->route('admin.approvePage', ['attendance_correct_request' => $edit->id])
                 ->with('message', '承認が完了しました。');


    } 
        public function approveBreakEdit($id)

    {
        
         $edit = BreakTimeEdit::findOrFail($id);
        
        $edits = BreakTimeEdit::where('user_id', $edit->user_id)
        ->where('target_date', $edit->target_date)
        ->where('edited_by_admin', 0)
        ->whereNull('approved_at')
        ->get();

        foreach($edits as $edit) {
            if ($edit->break_time_id && is_null($edit->new_clock_in) && is_null($edit->new_clock_out)) {
             $break = $edit->breakTime;
            if ($break) {
                    $break->delete();
                }
             }elseif (is_null($edit->break_time_id)) {
        if(!is_null($edit->new_clock_in) || !is_null($edit->new_clock_out)) {
        
            $attendance = Attendance::firstOrCreate(
                ['user_id' => $edit->user_id, 'date' => $edit->target_date],
                ['clock_in' => null, 'clock_out' => null]
            );
            BreakTime::create([
                'user_id' => $edit->user_id,
                'attendance_id' => $attendance->id,
               'clock_in' => $edit->new_clock_in,
                'clock_out' => $edit->new_clock_out,
                ]);
            }
        }else {
                $break = $edit->breakTime;
                 if ($break) {
                    $break->clock_in = $edit->new_clock_in ?? $break->clock_in;
                    $break->clock_out = $edit->new_clock_out ?? $break->clock_out;
                    $break->save();
                }
            }
    
        $edit->approved_at = now();
        $edit->save();
        }
         return redirect()->back()->with('message', '出勤データの承認が完了しました。');
    }

    public function downloadCsv(Request $request ,$id)
    {
        
        $user = User::findOrFail($id);

        $monthParam = str_replace('/', '-', $request->query('month'));
        
        $targetMonth = $monthParam ? Carbon::parse($monthParam . '-01'): now();

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

           
            $attendanceData[] = [
                'date' => $date->format('m/d') . '(' . $weekMap[$date->format('D')] . ')' ,
                'clockIn' => $clockIn ? $clockIn->format('H:i') : '',
                'clockOut' => $clockOut ? $clockOut->format('H:i') : '',
                'breakTime' => ($clockIn && $clockOut) ? $this->formatMinutes($totalBreakMinutes) : '',
                'workingTime' => ($clockIn && $clockOut) ?$this->formatMinutes($workingMinutes) : '',
            ];
        }
        $response = new StreamedResponse(function () use ($attendanceData, $user) {
            $handle = fopen('php://output', 'W');

            stream_filter_append($handle, 'convert.iconv.utf-8/cp932//TRANSLIT');

            fputcsv($handle, ['氏名', '日付', '出勤', '退勤', '休憩', '合計']);
            foreach($attendanceData as $day) {
                fputcsv($handle, [
                    $user->name,
                    $day['date'],
                    $day['clockIn'],
                    $day['clockOut'],
                    $day['breakTime'],
                    $day['workingTime'],

                ]);
            }
            fclose($handle);
        });

        $safeUserName = preg_replace('/[^\w\-]/u', '_' , $user->name);
       $filename = 'attendance_' . $safeUserName . '_' . $targetMonth->format('Y_m') . '.csv';

       $response->headers->set('Content-Type', 'text/csv; charset=Shift-JIS');
       $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');

       return $response;
    }
        

    
}