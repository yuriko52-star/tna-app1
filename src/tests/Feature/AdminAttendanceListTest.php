<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    /*public function test_example()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
    */
    private function formatMinutes($minutes)
{
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    return sprintf('%d:%02d', $hours, $mins);
}
// その日になされた全ユーザーの勤怠情報が正確に確認できる
    public function test_admin_can_view_users_attendances()
    {
         $admin = User::factory()->create(['role' => 'admin']);
        $users = User::factory()->count(3)->create();
       
        $targetDay = Carbon::parse('2025-04-06');
        foreach($users as $user) {
            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => $targetDay,
                'clock_in' => $targetDay->copy()->setTime(9, 0),
                'clock_out' => $targetDay->copy()->setTime(18, 0),
            ]);
            BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
                'clock_in' => $targetDay->copy()->setTime(10, 0),
                'clock_out' => $targetDay->copy()->setTime(10, 30),
            ]);
            BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
                'clock_in' => $targetDay->copy()->setTime(12, 0),
                'clock_out' => $targetDay->copy()->setTime(13, 0),
            ]);
             }
        $response  = $this->actingAs($admin, 'admin')->get(route('admin.attendance.index',['day' => $targetDay->format('Y-m-d')]));
        $response->assertStatus(200);
        $response->assertViewIs('admin.list');
        $response->assertViewHas('attendanceData', function ($attendanceData) use ($users) {
            foreach($users as $user) 
            {$data = collect($attendanceData)->firstWhere('user_name', $user->name);
                if(!$data) {
                    return false;
                }
                if ($data['clockIn'] !== '09:00' ||
                    $data['clockOut'] !== '18:00' ||
                   $data['breakTime'] !==$this->formatMinutes(90)  ||
                   $data['workingTime'] !== $this->formatMinutes(450)) {
                    return false;
                   }
            }
            
            return true;
        });
    }
    public function test_attendance_list_displays_current_date()
    {
        $targetDay = Carbon::today();

        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.list');

        $response->assertViewHas('thisDay', function($thisDay) use ($targetDay) {
           return $thisDay === $targetDay->format('Y/m/d'); 
        });
    }
    public function test_admin_can_view_previous_day_attendances()
    {
        Carbon::setTestNow('2025-04-06');
        $admin = User::factory()->create(['role' => 'admin']);

        $targetDay = Carbon::today();
        $previousDay = $targetDay->copy()->subDay();

        $users = User::factory()->count(3)->create();

        foreach($users as $user) {
            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => $previousDay,
                'clock_in' => $previousDay->copy()->setTime(9,0),
                'clock_out' => $previousDay->copy()->setTime(18,0),
            ]);
            BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
                'clock_in' => $previousDay->copy()->setTime(10,0),
                'clock_out' => $previousDay->copy()->setTime(10,30),
            ]);
            BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
                'clock_in' => $previousDay->copy()->setTime(12,0),
                'clock_out' => $previousDay->copy()->setTime(13,0),
            ]);
        }

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.index', ['day' => $previousDay->format('Y-m-d')]));
        $response->assertStatus(200);
        $response->assertViewIs('admin.list');

        $response->assertViewHas('thisDay', function($thisDay) use ($previousDay) {
            return $thisDay === $previousDay->format('Y/m/d');
        });

        $response->assertViewHas('attendanceData', function ($attendanceData) use ($users) {
            foreach($users as $user) {
                $data = collect($attendanceData)->firstWhere('user_name', $user->name);
                if(!$data) {
                    return false;
                }

                if($data['clockIn'] !== '09:00' ||
                $data['clockOut'] !== '18:00' ||
                $data['breakTime'] !== $this->formatMinutes(90) ||
                $data['workingTime'] !== $this->formatMinutes(450)) {
                    return false;
                }
            }
            return true;
        });
        Carbon::setTestNow();
    }
    public function test_admin_can_view_next_day_attendances()
    {
        Carbon::setTestNow('2025-04-06');
        $admin = User::factory()->create(['role' => 'admin']);

        $targetDay = Carbon::today();
        $nextDay = $targetDay->copy()->addDay();

        $users = User::factory()->count(3)->create();

        foreach($users as $user) {
            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => $nextDay,
                'clock_in' => $nextDay->copy()->setTime(9,0),
                'clock_out' => $nextDay->copy()->setTime(18,0),
            ]);
            BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
                'clock_in' => $nextDay->copy()->setTime(10,0),
                'clock_out' => $nextDay->copy()->setTime(10,30),
            ]);
            BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
                'clock_in' => $nextDay->copy()->setTime(12,0),
                'clock_out' => $nextDay->copy()->setTime(13,0),
            ]);
        }

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.index', ['day' => $nextDay->format('Y-m-d')]));
        $response->assertStatus(200);
        $response->assertViewIs('admin.list');

        $response->assertViewHas('thisDay', function($thisDay) use ($nextDay) {
            return $thisDay === $nextDay->format('Y/m/d');
        });

        $response->assertViewHas('attendanceData', function ($attendanceData) use ($users) {
            foreach($users as $user) {
                $data = collect($attendanceData)->firstWhere('user_name', $user->name);
                if(!$data) {
                    return false;
                }

                if($data['clockIn'] !== '09:00' ||
                $data['clockOut'] !== '18:00' ||
                $data['breakTime'] !== $this->formatMinutes(90) ||
                $data['workingTime'] !== $this->formatMinutes(450)) {
                    return false;
                }
            }
            return true;
        });
        Carbon::setTestNow();
    }
    public function test_admin_can_view_attendance_detail()
    // 勤怠詳細画面に表示されるデータが選択したものになっている
    {
        Carbon::setTestNow('2025-04-06');

        $admin = User::factory()->create(['role' => 'admin']);
        $targetDay = Carbon::today();
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $targetDay,
            'clock_in' => $targetDay->copy()->setTime(9, 0),
            'clock_out' => $targetDay->copy()->setTime(18, 0),
        ]);

    
        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'clock_in' => $targetDay->copy()->setTime(10, 0),
            'clock_out' => $targetDay->copy()->setTime(10, 30),
        ]);

    
        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'clock_in' => $targetDay->copy()->setTime(12, 0),
            'clock_out' => $targetDay->copy()->setTime(13, 0),
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertViewIs('admin.detail');

        $response->assertViewHas('attendance', function ($data) use ($user, $attendance) {
            return $data->user->name === $user->name &&
            $data->clock_in  === '2025-04-06 09:00:00' &&
            $data->clock_out === '2025-04-06 18:00:00' &&
            count($data->breakTimes) ===2 &&
            $data->breakTimes[0]->clock_in === '2025-04-06 10:00:00' &&
            $data->breakTimes[0]->clock_out === '2025-04-06 10:30:00' &&
            $data->breakTimes[1]->clock_in === '2025-04-06 12:00:00' &&
            $data->breakTimes[1]->clock_out === '2025-04-06 13:00:00' ;

        });
        
        Carbon::setTestNow();
    }
    public function test_admin_can_view_users_page()
    {
        $admin = User::factory()->create();
        $users = User::factory()->count(3)->create();
        $response  = $this->actingAs($admin, 'admin')->get(route('admin.staff.list'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.staff-list');

        foreach($users as $user) {
            $response->assertSeeText($user->name);
            $response->assertSeeText($user->email);
        }
    }
// ユーザーの勤怠情報が正しく表示される
     public function test_admin_can_view_users_monthly_attendance()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        
        $targetMonth = Carbon::create(2025,5);
        $startOfMonth = $targetMonth->copy()->startOfMonth();
        $endOfMonth = $targetMonth->copy()->endOfMonth();
        $attendanceIds = [];
        for ($date = $startOfMonth->copy(); $date <= $endOfMonth; $date->addDays(2)) {
                $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => $date->toDateString(),
                'clock_in' => $date->copy()->setTime(9, 0),
                'clock_out' => $date->copy()->setTime(17, 0),
            
                ]);
            BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
                'clock_in' => $date->copy()->setTime(10, 0),
                'clock_out' => $date->copy()->setTime(10, 30),
                ]);
            BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
                'clock_in' => $date->copy()->setTime(12, 0),
                'clock_out' => $date->copy()->setTime(13, 0),
                ]);
                $attendanceIds[$date->toDateString()] = $attendance->id; 
            }


        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.staff',[
                    'id' => $user->id,
                    'month' => $targetMonth->format('Y-m')
                ]));
         $response->assertStatus(200);
        $response->assertViewIs('admin.month-list');
        $expectedBreakTime = '1:30';
        $expectedWorkTime = '6:30';

        for ($date = $startOfMonth->copy(); $date <= $endOfMonth; $date->addDay()) {
            $response->assertSee($date->format('m/d'));

            $weekday = $date->format('D');
            $weekdayJapanese = [
            'Sun' => '日', 'Mon' => '月', 'Tue' => '火',
            'Wed' => '水', 'Thu' => '木', 'Fri' => '金', 'Sat' => '土',
            ][$weekday];
            $response->assertSee($weekdayJapanese);
            if (array_key_exists($date->toDateString(), $attendanceIds)) {
                $attendanceId = $attendanceIds[$date->toDateString()];
                $response->assertSee('<a href="' . url('/admin/attendance/' . $attendanceId) . '" class="data-link">詳細</a>', false);
                $response->assertSee('09:00');
                $response->assertSee('17:00');
                $response->assertSee($expectedBreakTime);
                $response->assertSee($expectedWorkTime);
            
            } else {
            
                $response->assertSee('<a href="' . route('admin.attendance.detailByDateForAdmin', [
                'id' => $user->id,
                'date' => $date->toDateString()
            ]) . '" class="data-link">詳細</a>', false);
        }
    }
}

    public function test_admin_can_view_previous_monthly_attendance_list()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        
    $previousMonth = Carbon::create(2025, 4, 1); 
        $startOfMonth = $previousMonth->copy()->startOfMonth();
        $endOfMonth = $previousMonth->copy()->endOfMonth();
         $attendanceIds = [];
        for ($date = $startOfMonth->copy(); $date <= $endOfMonth; $date->addDays(2)) {
            $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $date->toDateString(),
            'clock_in' => $date->copy()->setTime(9, 0),
            'clock_out' => $date->copy()->setTime(17, 0),
            
            ]);
         BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'clock_in' => $date->copy()->setTime(10, 0),
            'clock_out' => $date->copy()->setTime(10, 30),
            
             ]);
         BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'clock_in' => $date->copy()->setTime(12, 0),
            'clock_out' => $date->copy()->setTime(13, 0),
            
            ]);
             $attendanceIds[$date->toDateString()] = $attendance->id;
        }
        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.staff', [
                'id' => $user->id,
                'month' => $previousMonth->format('Y-m')
        ]));

        
         $response->assertStatus(200);
        $response->assertViewIs('admin.month-list');

        $expectedBreakTime = '1:30';
        $expectedWorkTime = '6:30';

        for ($date = $startOfMonth->copy(); $date <= $endOfMonth; $date->addDay()) {
            $response->assertSee($date->format('m/d'));

            $weekday = $date->format('D');
            $weekdayJapanese = [
            'Sun' => '日', 'Mon' => '月', 'Tue' => '火',
            'Wed' => '水', 'Thu' => '木', 'Fri' => '金', 'Sat' => '土',
            ][$weekday];
            $response->assertSee($weekdayJapanese);
             
         if (array_key_exists($date->toDateString(), $attendanceIds)) {
                $attendanceId = $attendanceIds[$date->toDateString()];
                $response->assertSee('<a href="' . url('/admin/attendance/' . $attendanceId) . '" class="data-link">詳細</a>', false);
                $response->assertSee('09:00');
                $response->assertSee('17:00');
                $response->assertSee($expectedBreakTime);
                $response->assertSee($expectedWorkTime);
            } else {$response->assertSee('<a href="' . route('admin.attendance.detailByDateForAdmin', [
                'id' => $user->id,
                'date' => $date->toDateString()
            ]) . '" class="data-link">詳細</a>', false);}
            $response->assertSee(Carbon::parse($previousMonth)->format('Y/m'));

        }

    }

    
     public function test_admin_can_view_next_monthly_attendance_list()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        
        $nextMonth = Carbon::create(2025, 6, 1); 
        $startOfMonth = $nextMonth->copy()->startOfMonth();
        $endOfMonth = $nextMonth->copy()->endOfMonth();
        $attendanceIds = [];

        for ($date = $startOfMonth->copy(); $date <= $endOfMonth; $date->addDays(2)) {
            $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $date->toDateString(),
            'clock_in' => $date->copy()->setTime(9, 0),
            'clock_out' => $date->copy()->setTime(17, 0),
            
            ]);
         BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'clock_in' => $date->copy()->setTime(10, 0),
            'clock_out' => $date->copy()->setTime(10, 30),
            
             ]);
         BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'clock_in' => $date->copy()->setTime(12, 0),
            'clock_out' => $date->copy()->setTime(13, 0),
            
            ]);
            $attendanceIds[$date->toDateString()] = $attendance->id;
        }
        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.staff', [
        'id' => $user->id,
        'month' => $nextMonth->format('Y-m')
        ]));
        
         $response->assertStatus(200);
        $response->assertViewIs('admin.month-list');

        $expectedBreakTime = '1:30';
        $expectedWorkTime = '6:30';

        for ($date = $startOfMonth->copy(); $date <= $endOfMonth; $date->addDay()) {
            $response->assertSee($date->format('m/d'));

            $weekday = $date->format('D');
            $weekdayJapanese = [
            'Sun' => '日', 'Mon' => '月', 'Tue' => '火',
            'Wed' => '水', 'Thu' => '木', 'Fri' => '金', 'Sat' => '土',
            ][$weekday];
            $response->assertSee($weekdayJapanese);
            if (array_key_exists($date->toDateString(), $attendanceIds)) {
                $attendanceId = $attendanceIds[$date->toDateString()];
                $response->assertSee('<a href="' . url('/admin/attendance/' . $attendanceId) . '" class="data-link">詳細</a>', false);
                $response->assertSee('09:00');
                $response->assertSee('17:00');
                $response->assertSee($expectedBreakTime);
                $response->assertSee($expectedWorkTime);
            } else {$response->assertSee('<a href="' . route('admin.attendance.detailByDateForAdmin', [
                'id' => $user->id,
                'date' => $date->toDateString()
            ]) . '" class="data-link">詳細</a>', false);
            }

            $response->assertSee(Carbon::parse($nextMonth)->format('Y/m'));
        }
    }

    public function test_attendance_detail_link_navigates_to_detail_page()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
         $targetMonth = Carbon::create(2025,5);

        $attendance =  Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'clock_in' => Carbon::now()->setTime(9, 0),
            'clock_out' => Carbon::now()->setTime(17, 0),
        ]);
        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'clock_in' => Carbon::now()->setTime(10, 0),
            'clock_out' => Carbon::now()->setTime(10, 30),
            
        ]);
         BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'clock_in' => Carbon::now()->setTime(12, 0),
            'clock_out' =>Carbon::now()->setTime(13, 0),
            
        ]);
        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.staff', [
        'id' => $user->id,
        'month' => $targetMonth->format('Y-m')
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('admin.month-list');
        
         $response->assertSee(route('admin.attendance.detail',['id' => $attendance->id]));

         $response = $this->get(route('admin.attendance.detail',['id' => $attendance->id]));
        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('17:00');
        $response->assertSee('10:00');
        $response->assertSee('10:30');
        $response->assertSee('12:00');$response->assertSee('13:00');
    }

}    

