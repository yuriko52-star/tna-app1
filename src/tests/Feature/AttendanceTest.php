<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceTest extends TestCase
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

    public function setUp():void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2025,5,1,8,0));
        Carbon::setLocale('ja');
    }


    public function testAttendancePageDisplaysCurrentDateAndTimeCorrectly()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Carbon::setTestNow(Carbon::create(2025,5,1,8,0,0));

        $response = $this->get('/attendance');

        $response->assertStatus(200);

        $expectedDate = now()->isoFormat('YYYY年M月D日(ddd)');

        $expectedTime = now()->format('H:i');

        $response->assertSee($expectedDate);
        $response->assertSee($expectedTime);
    }

    public function testDisplaysStatusAsOffDutyWhenNoAttendanceData()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    public function testDisplaysStatusAsWorkingWhenClockedIn()
    {
        
        
        $user = User::factory()->create();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'clock_in' => Carbon::now()->setTime(8, 0),
            'clock_out' => null,
        ]);
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    public function testDisplaysStatusAsOnBreakDuringBreakTime()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDAteString(),
            'clock_in' => Carbon::now()->setTime(8, 0),
            'clock_out'=> null,
        ]);
        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'clock_in' => Carbon::now()->setTime(10, 0),
            'clock_out' => null,
        ]);
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
    }

    public function testDisplaysStatusAsClockedOut()
    {
        $user = User::factory()->create();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDAteString(),
            'clock_in' => Carbon::now()->setTime(8,0),
            'clock_out' => Carbon::now()->setTime(17,0),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
    }
}
