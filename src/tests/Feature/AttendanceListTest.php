<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;


class AttendanceListTest extends TestCase
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

    public function test_attendance_list_displays_user_attendance()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $targetMonth = Carbon::create(2025, 5, 1);
        $startOfMonth = $targetMonth->copy()->startOfMonth();
        $endOfMonth = $targetMonth->copy()->endOfMonth();
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
        }
        $response = $this->get('/attendance/list?month=2025-05');
         $response->assertStatus(200);

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

            if ($date->day % 2 === 1) {
        
                $response->assertSee('09:00');
                $response->assertSee('17:00');
                $response->assertSee($expectedBreakTime);
                $response->assertSee($expectedWorkTime);
            }
        } 
    }

    public function test_attendance_list_displays_current_month_data()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $dates = [
            $startOfMonth->copy()->addDays(0),
            $startOfMonth->copy()->addDays(5),
            $startOfMonth->copy()->addDays(10),
        ];
        foreach($dates as $date) {
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
        }$response = $this->get('/attendance/list');
         $response->assertStatus(200);

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
            foreach($dates as $date){
                $response->assertSee('09:00');
                $response->assertSee('17:00');
                $response->assertSee($expectedBreakTime);
                $response->assertSee($expectedWorkTime);
            }
            $response->assertSee($now->format('Y/m'));
        } 
    }

    public function test_attendance_list_displays_previous_month()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        
        $previousMonth = Carbon::create(2025, 4, 1); 
        $startOfMonth = $previousMonth->copy()->startOfMonth();
        $endOfMonth = $previousMonth->copy()->endOfMonth();
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
        }
        $response = $this->get('/attendance/list?month=' . $previousMonth);
         $response->assertStatus(200);

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
             if ($date->day % 2 === 1) {
        
                $response->assertSee('09:00');
                $response->assertSee('17:00');
                $response->assertSee($expectedBreakTime);
                $response->assertSee($expectedWorkTime);
            }
            $response->assertSee(Carbon::parse($previousMonth)->format('Y/m'));

        }

    }

     public function test_attendance_list_displays_next_month()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        
        $nextMonth = Carbon::create(2025, 6, 1); 
        $startOfMonth = $nextMonth->copy()->startOfMonth();
        $endOfMonth = $nextMonth->copy()->endOfMonth();
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
        }
        $response = $this->get('/attendance/list?month=' . $nextMonth);
         $response->assertStatus(200);

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
             if ($date->day % 2 === 1) {
        
                $response->assertSee('09:00');
                $response->assertSee('17:00');
                $response->assertSee($expectedBreakTime);
                $response->assertSee($expectedWorkTime);
            }
            $response->assertSee(Carbon::parse($nextMonth)->format('Y/m'));
        }
    }
    public function test_attendance_detail_link_navigates_to_detail_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

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
        

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee(route('user.attendance.detail',['id' => $attendance->id]));

        $response = $this->get(route('user.attendance.detail',['id' => $attendance->id]));
        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('17:00');

    }
    
}