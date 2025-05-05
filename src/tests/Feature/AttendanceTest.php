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

    public function testUserCanClockIn()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $this->assertMatchesRegularExpression('/<button\s+class="work-btn".*?>\s*出勤\s*<\/button>/',
            $response->getContent()
        );

        $response = $this->post('/attendance/clock-in');
        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendances',[
            'user_id' => $user->id,
            'date' => now()->todateString(),
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }
    public function testUserCannotClockInTwiceInADay()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/attendance/clock-in');

        $this->post('/attendance/clock-out');

        $response = $this->get('/attendance');
        $this->assertDoesNotMatchRegularExpression('/<button\s+class="work-btn".*?>\s*出勤\s*<\/button>/',
            $response->getContent()
        );
    }
    public function testAdminCanViewUserClockInTime()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'clock_in' => Carbon::now()->setTime(8,0)->toDateTimeString(),
            
        ]);
        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSeeText($user->name);
        $response->assertSee(now()->format('H:i'));
    }
       
    public function testUserCanStartBreak()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'clock_in' => Carbon::now()->setTime(8, 0),
            'clock_out' => null,
        ]);
        
        $response = $this->get('/attendance');
        $this->assertMatchesRegularExpression('/<button\s+class="rest-btn".*?>\s*休憩入\s*<\/button>/',
            $response->getContent()
        );
        $this->post('/attendance/break-start');
        $breakTime = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'clock_in' => Carbon::now()->setTime(10, 0),
            'clock_out' => null,
        ]);
       

        $response = $this->get('/attendance');
        $response->assertSee('休憩中',false);
    }

    public function testUserCanTakeMultipleBreaksInADay()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

      

         $this->post('/attendance/clock-in');

         $this->post('/attendance/break-start');
         $this->post('/attendance/break-end');

         $this->post('/attendance/break-start');
         $this->post('/attendance/break-end');

        $response = $this->get('/attendance');

        $this->assertMatchesRegularExpression('/<button\s+class="rest-btn".*?>\s*休憩入\s*<\/button>/',
            $response->getContent()
        );
    }

    public function testUserCanEndBreak()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'clock_in' => Carbon::now()->setTime(8, 0),
            'clock_out' => null,
        ]);
   
        $breakTime = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'clock_in' => Carbon::now()->setTime(10, 0),
            'clock_out' => null,
        ]);
    
        $response = $this->get(route('user.attendance'));
        $response->assertStatus(200);
        $response->assertSee('休憩戻',false); 
   
    
        $this->post(route('attendance.breakEnd'));
        
        $latestBreak = BreakTime::where('attendance_id', $attendance->id)->whereNotNull('clock_out')->latest()->first();
        $this->assertNotNull($latestBreak);

        
        $response = $this->get(route('user.attendance'));
        $response->assertStatus(200);
        $response->assertSee('出勤中',false);
    }
    public function testUserCanEndMultipleBreaksInADay()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
          
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'clock_in' => Carbon::now()->setTime(8, 0),
            'clock_out' => null,
        ]);
    
        $breakTime = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'clock_in' => Carbon::now()->setTime(10, 0),
            'clock_out' => null,
        ]);
    
        $response = $this->get(route('user.attendance'));
        $response->assertStatus(200);
        $response->assertSee('休憩戻',false); 
   
    
        $this->post(route('attendance.breakEnd'));
        ;
        $latestBreak = BreakTime::where('attendance_id', $attendance->id)->whereNotNull('clock_out')->latest()->first();
        $this->assertNotNull($latestBreak);

    
        $breakTime = BreakTime::factory()->create([
        'attendance_id' => $attendance->id,
        'clock_in' => Carbon::now()->setTime(15, 0),
        'clock_out' => null,
        ]);
    
        $response = $this->get(route('user.attendance'));
        $response->assertStatus(200);
        $response->assertSee('休憩戻',false);
    
    
        $this->post(route('attendance.breakEnd'));
        $latestBreak = BreakTime::where('attendance_id', $attendance->id)->whereNotNull('clock_out')->latest()->first();
        
        $this->assertNotNull($latestBreak);
    }

    public function testBreakTimeTotalIsDisplayedInAttendanceList()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $clockIn = Carbon::create(2025,5,1,8,0,0);
        Carbon::setTestNow($clockIn);
        $this->post('/attendance/clock-in');

        $breakStart = Carbon::create(2025,5,1,10,0,0);
        Carbon::settestNow($breakStart);
        $this->post('/attendance/break-start');
        
        $breakEnd = $breakStart->copy()->addMinutes(15);
        Carbon::setTestNow($breakEnd);
        $this->post('/attendance/break-end');

        $this->post('/attendance/clock-out');
        Carbon::setTestNow();

        
        $response = $this->get('/attendance/list');
        $response->assertSee('0:15');
    }

        public function testUserCanClockOut()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

       
        Carbon::setTestNow(Carbon::create(2025, 5, 1, 8, 0));
        $this->post('/attendance/clock-in');

        $response = $this->get('/attendance');

        $this->assertMatchesRegularExpression('/<button\s+class="work-btn".*?>\s*退勤\s*<\/button>/',
            $response->getContent()
        );
         Carbon::setTestNow(Carbon::create(2025, 5, 1, 12, 0));
        $this->post('/attendance/break-start');
        Carbon::setTestNow(Carbon::create(2025, 5, 1, 13, 0));
        $this->post('/attendance/break-end');

        Carbon::setTestNow(Carbon::create(2025, 5, 1, 16, 0));
        $response = $this->post('/attendance/clock-out');
       
        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendances',[
            'user_id' => $user->id,
            'date' => '2025-05-01',
            'clock_in' => '2025-05-01 08:00:00',
            'clock_out' => '2025-05-01 16:00:00',
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
    }
        public function testAdminCanViewUserClockOutTime()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
             'clock_in' => Carbon::now()->setTime(8,0)->toDateTimeString(),
             'clock_out' => Carbon::now()->setTime(16,0)->toDateTimeString(),
        ]);
        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSeeText($user->name);
        $response->assertSee(now()->format('H:i'));
    }
       
}