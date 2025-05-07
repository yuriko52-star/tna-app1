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
    
}
