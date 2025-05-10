<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use App\Models\AttendanceEdit;
use App\Models\BreakTimeEdit;

class AttendanceDetailTest extends TestCase
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
    
    public function test_user_name_is_displayed_correctly()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $attendance = Attendance::factory()->create([
        'user_id' => $user->id,
        'date' => '2025-05-01',
    ]);

    $response = $this->get(route('user.attendance.detail', ['id' => $attendance->id]));
    $response->assertStatus(200);
    $response->assertSee($user->name);
}

public function test_attendance_date_is_displayed_correctly()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $attendance = Attendance::factory()->create([
        'user_id' => $user->id,
        'date' => '2025-05-01',
    ]);

    $response = $this->get(route('user.attendance.detail', ['id' => $attendance->id]));
    $response->assertStatus(200);
    $response->assertSee('5月1日');
}

public function test_attendance_time_is_displayed_correctly()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $attendance = Attendance::factory()->create([
        'user_id' => $user->id,
        'date' => '2025-05-01',
        'clock_in' => Carbon::now()->setTime(9, 0),
        'clock_out' => Carbon::now()->setTime(17, 0),
    ]);

    $response = $this->get(route('user.attendance.detail', ['id' => $attendance->id]));
    $response->assertStatus(200);
    $response->assertSee('09:00');
    $response->assertSee('17:00');
}

public function test_break_times_are_displayed_correctly()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $attendance = Attendance::factory()->create([
        'user_id' => $user->id,
        'date' => '2025-05-01',
        'clock_in' => Carbon::create(2025, 5, 1, 9, 0, 0),
        'clock_out' => Carbon::create(2025, 5, 1, 17, 0, 0),
        
    ]);

    BreakTime::factory()->create([
        'attendance_id' => $attendance->id,
        'clock_in' => Carbon::create(2025, 5, 1, 10, 0, 0),
        'clock_out' => Carbon::create(2025, 5, 1, 10, 30, 0),
       
    ]);

    BreakTime::factory()->create([
        'attendance_id' => $attendance->id,
        'clock_in' => Carbon::create(2025, 5, 1, 12, 0, 0),
        'clock_out' => Carbon::create(2025, 5, 1, 13, 0, 0),
        
    ]);

    $attendance->refresh();
    $attendance->load('breakTimes');

    $response = $this->get(route('user.attendance.detail', ['id' => $attendance->id]));

    $response->assertStatus(200);

    $response->assertSee('09:00');
    $response->assertSee('17:00');
    $response->assertSee('10:00');
    $response->assertSee('10:30');
    $response->assertSee('12:00');
    $response->assertSee('13:00');
}

    public function test_clock_in_after_clock_out_shows_error()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-05-01',
        ]);

        $response = $this->patch(route('attendance.update',['id' => $attendance->id]), [
            'clock_in' => '18:00',
            'clock_out' => '09:00',
            'reason' => 'テスト',
        ]);

        $response->assertSessionHasErrors(['clock_time_invalid']);
    }

    public function test_break_start_after_clock_out_shows_error()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-05-01',
        ]);

        $response = $this->patch(route('attendance.update' ,['id' => $attendance->id]),[
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'reason' => 'テスト',
            'breaks' => [
                [
                    'clock_in' => '19:00',
                    'clock_out' => '20:00',
                ]
                ],
            ]);

        $response->assertSessionHasErrors(['breaks.0.outside_working_time']);
    }
    public function test_break_end_after_clock_out_shows_error()
    {
         $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-05-01',
        ]);

        $response = $this->patch(route('attendance.update' ,['id' => $attendance->id]),[
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'reason' => 'テスト',
            'breaks' => [
                [
                    'clock_in' => '12:00',
                    'clock_out' => '20:00',
                ]
                ],
            ]);

        $response->assertSessionHasErrors(['breaks.0.outside_working_time']);
    }
    public function test_reason_required_validation()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-05-01',
        ]);

        $response = $this->patch(route('attendance.update' ,['id' => $attendance->id]),[
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'reason' => '',
            'breaks' => [
                [
                    'clock_in' => '12:00',
                    'clock_out' => '13:00',
                ]
                ],
            ]);

        $response->assertSessionHasErrors(['reason']);
    }
    public function test_edit_request_is_created()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date'=> '2025-05-01',
        ]);

        $response = $this->patch(route('attendance.update' ,['id' => $attendance->id]),[
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'reason' => '修正申請',
            'breaks' => [
                [
                    'clock_in' => '12:00',
                    'clock_out' => '13:00',
                ]
                ],
            ]);
            $response->assertRedirect(route('user.stamp_correction_request.list', ['tab' => 'waiting']));
            $this->assertDatabaseHas('attendance_edits',[
                'user_id' => $user->id,
                'target_date' => '2025-05-01',
                'new_clock_in' => Carbon::createFromFormat('Y-m-d H:i', '2025-05-01 09:00')->toDateTimeString(),
                'new_clock_out' => Carbon::createFromFormat('Y-m-d H:i', '2025-05-01 18:00')->toDateTimeString(),
                'edited_by_admin' => false,
                'approved_at' => null,
                'request_date' => Carbon::now()->format('Y-m-d'),
                'reason' => '修正申請',
            ]);
            $this->assertDatabaseHas('break_time_edits', [
                'user_id' => $user->id,
                'target_date' => '2025-05-01',
                'new_clock_in' => Carbon::createFromFormat('Y-m-d H:i', '2025-05-01 12:00')->toDateTimeString(),
                'new_clock_out' => Carbon::createFromFormat('Y-m-d H:i', '2025-05-01 13:00')->toDateTimeString(),
                'edited_by_admin' => false,
                'approved_at' => null,
                'request_date' => Carbon::now()->format('Y-m-d'),
                'reason' => '修正申請',

            ]);
    }
    public function test_waiting_tab_shows_user_requests()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $requestCount = 3;
        $startDate = Carbon::create(2025, 4, 1);
       
        for ($i = 0; $i < $requestCount; $i++) {
            $targetDate = $startDate->copy()->addDays($i * 3); 
            $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            
            'date' => $targetDate->toDateString(),
        ]);

        
        AttendanceEdit::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'target_date' => $targetDate->toDateString(), 
            'request_date' => Carbon::create(2025, 5, 6)->toDateString(), 
            'new_clock_in' => $targetDate->copy()->setTime(9, 0)->toDateTimeString(), 
            'new_clock_out' => $targetDate->copy()->setTime(18, 0)->toDateTimeString(), 
            'reason' => '修正申請',
            'edited_by_admin' => false,
            'approved_at' => null,
        ]);
        $breakTime = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            ]);
        
        BreakTimeEdit::factory()->create([
            'user_id' => $user->id,
            'break_time_id' => $breakTime->id,
            'target_date' => $targetDate->toDateString(), 
            'request_date' => Carbon::create(2025, 5, 6)->toDateString(), 
            'new_clock_in' => $targetDate->copy()->setTime(12, 0)->toDateTimeString(), 
            'new_clock_out' => $targetDate->copy()->setTime(13, 0)->toDateTimeString(), 
            'reason' => '修正申請',
            'edited_by_admin' => false,
            'approved_at' => null,
            ]);
        }
        
        $response = $this->get(route('user.stamp_correction_request.list', ['tab' => 'waiting']));

        $response->assertStatus(200);
        $edits = AttendanceEdit::where('user_id', $user->id)->get();
        $breakEdits = BreakTimeEdit::where('user_id', $user->id)->get();


        foreach($edits as $edit) {
            $response->assertSee($user->name);
            $response->assertSee(Carbon::parse($edit->target_date)->format('Y/m/d'));
            $response->assertSee(Carbon::create(2025, 5, 6)->format('Y/m/d'));
            $response->assertSee($edit->reason);
        }
        foreach($breakEdits as $breakEdit) {
            $response->assertSee($user->name);
            $response->assertSee(Carbon::parse($breakEdit->target_date)->format('Y/m/d'));
            $response->assertSee(Carbon::create(2025, 5, 6)->format('Y/m/d'));
             $response->assertSee($breakEdit->reason);
        }
    }

    public function test_approved_tab_shows_approved_requests()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $requestCount = 3;

        
        $startDate = Carbon::create(2025, 4, 1);
        $requestCount = 3;

        for ($i = 0; $i < $requestCount; $i++) {
            $targetDate = $startDate->copy()->addDays($i * 3); 
         $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
           
            'date' => $targetDate->toDateString(),
        ]);
         AttendanceEdit::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'target_date' => $targetDate->toDateString(), 
            'request_date' => Carbon::create(2025, 5, 6)->toDateString(), 
            'new_clock_in' => $targetDate->copy()->setTime(9, 0)->toDateTimeString(), 
            'new_clock_out' => $targetDate->copy()->setTime(18, 0)->toDateTimeString(), 
            'reason' => '修正申請',
            'edited_by_admin' => false,
            'approved_at' => now(),
        ]);
        $breakTime = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            ]);
        
         BreakTimeEdit::factory()->create([
            'user_id' => $user->id,
            'break_time_id' => $breakTime->id,
            'target_date' => $targetDate->toDateString(), 
            'request_date' => Carbon::create(2025, 5, 6)->toDateString(), 
            'new_clock_in' => $targetDate->copy()->setTime(12, 0)->toDateTimeString(),
            'new_clock_out' => $targetDate->copy()->setTime(13, 0)->toDateTimeString(), 
            'reason' => '修正申請',
            'edited_by_admin' => false,
            'approved_at' => now(),
            ]);
        }
        

        $response = $this->get(route('user.stamp_correction_request.list', ['tab' => 'approved']));

        $response->assertStatus(200);

        $edits = AttendanceEdit::where('user_id', $user->id)->get();
        $breakEdits = BreakTimeEdit::where('user_id', $user->id)->get();

        foreach($edits as $edit) {
            $response->assertSee($user->name);
            $response->assertSee(Carbon::parse($edit->target_date)->format('Y/m/d'));
            $response->assertSee(Carbon::create(2025, 5, 6)->format('Y/m/d'));
            $response->assertSee($edit->reason);
        }
        foreach($breakEdits as $breakEdit) {
            $response->assertSee($user->name);
            $response->assertSee(Carbon::parse($breakEdit->target_date)->format('Y/m/d'));
            $response->assertSee(Carbon::create(2025, 5, 6)->format('Y/m/d'));
             $response->assertSee($breakEdit->reason);
        }
    }
    public function test_edit_detail_link_redirects_to_detail_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

         $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-05-01',
        ]);
         $targetDate = Carbon::create(2025, 5, 1);
        $edit = AttendanceEdit::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'target_date' => $targetDate->toDateString(), 
            'request_date' => Carbon::create(2025, 5, 6)->toDateString(), 
            'new_clock_in' => $targetDate->copy()->setTime(9, 0)->toDateTimeString(), 
            'new_clock_out' => $targetDate->copy()->setTime(18, 0)->toDateTimeString(), 
            'reason' => '修正申請',
            'edited_by_admin' => false,
            'approved_at' => null,
        ]);
         $breakTime = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
        ]);
        $breakEdit =  breakTimeEdit::factory()->create([
            'user_id' => $user->id,
            'break_time_id' => $breakTime->id,
            'target_date' => $targetDate->toDateString(), 
            'request_date' => Carbon::create(2025, 5, 6)->toDateString(), 
            'new_clock_in' => $targetDate->copy()->setTime(12, 0)->toDateTimeString(), 
            'new_clock_out' => $targetDate->copy()->setTime(13, 0)->toDateTimeString(), 
            'reason' => '修正申請',
            'edited_by_admin' => false,
            'approved_at' => null,
        ]);
        $response = $this->get(route('user.stamp_correction_request.list', ['tab' => 'waiting']));
        

        $response->assertStatus(200);
        $response->assertSee(route('attendance.editDetail',['date' => $edit->target_date]));

        $response = $this->get(route('attendance.editDetail',['date' => $edit->target_date]));
        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee(Carbon::parse($edit->target_date)->format('n月j日'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
       
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('修正申請');
         



    }
}
