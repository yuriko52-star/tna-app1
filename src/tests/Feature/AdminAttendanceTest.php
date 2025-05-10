<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
Use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceEdit;
use App\Models\BreakTimeEdit;
use Carbon\Carbon;

class AdminAttendanceTest extends TestCase
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
    public function test_clock_in_after_clock_out_shows_error()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $this->actingAs($admin, 'admin');

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-05-01',
        ]);

        $response = $this->patch(route('admin.attendance.update',['id' => $attendance->id]), [
            'clock_in' => '18:00',
            'clock_out' => '09:00',
            'reason' => 'テスト',
        ]);

        $response->assertSessionHasErrors(['clock_time_invalid']);
    }

    public function test_break_start_after_clock_out_shows_error()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $this->actingAs($admin, 'admin');

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-05-01',
        ]);

        $response = $this->patch(route('admin.attendance.update' ,['id' => $attendance->id]),[
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
         $admin = User::factory()->create(['role' => 'admin']);
         $user = User::factory()->create();
        $this->actingAs($admin, 'admin');

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-05-01',
        ]);

        $response = $this->patch(route('admin.attendance.update' ,['id' => $attendance->id]),[
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
         $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $this->actingAs($admin, 'admin');

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-05-01',
        ]);

        $response = $this->patch(route('admin.attendance.update' ,['id' => $attendance->id]),[
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
    public function test_admin_can_confirm_that_waiting_tab_shows_user_requests()
    {
        $admin = User::factory()->create(['role' => 'admin']);

       $users = collect([
        User::factory()->create(['name' => '近藤 春香']),
        User::factory()->create(['name' => '山岸 里佳']),
        User::factory()->create(['name' => '野村 知実']),
    ]);
       $startDate = Carbon::create(2025, 4, 1);
        
        foreach($users as $index => $user) {
        
            $targetDate = $startDate->copy()->addDays($index * 5);
            $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $targetDate->toDateString(),
            ]);
          
             AttendanceEdit::factory()->create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'target_date' => $targetDate->toDateString(), 
                'request_date' => Carbon::now()->toDateString(), 
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
                'request_date' => Carbon::now()->toDateString(), 
                'new_clock_in' => $targetDate->copy()->setTime(12, 0)->toDateTimeString(), 
                'new_clock_out' => $targetDate->copy()->setTime(13, 0)->toDateTimeString(), 
                'reason' => '修正申請',
                'edited_by_admin' => false,
                'approved_at' => null,
            ]);
        }
         
        $response = $this->actingAs($admin,'admin')->get(route('admin.stamp_correction_request.list', ['tab' => 'waiting']));

        $response->assertStatus(200);
        foreach($users as $user) {
            $response->assertSee($user->name);
        
            $edits = AttendanceEdit::where('user_id', $user->id)->get();
            foreach($edits as $edit) {
                $formattedDate = Carbon::parse($edit->target_date)->format('Y/m/d');
                $response->assertSee($formattedDate);
                $response->assertSee(Carbon::now()->format('Y/m/d'));
                $response->assertSee($edit->reason);
            }
                $breakEdits = BreakTimeEdit::where('user_id', $user->id)->get();
            foreach($breakEdits as $breakEdit) {
                $formattedDate = Carbon::parse($breakEdit->target_date)->format('Y/m/d');
                $response->assertSee($formattedDate);
            
            $response->assertSee(Carbon::now()->format('Y/m/d'));
             $response->assertSee($breakEdit->reason);
            }
        }
    }
    public function test_admin_can_confirm_that_approved_tab_shows_user_requests()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        
        $users = collect([
        User::factory()->create(['name' => '近藤 春香']),
        User::factory()->create(['name' => '山岸 里佳']),
        User::factory()->create(['name' => '野村 知実']),
    ]);
        $startDate = Carbon::create(2025, 4, 1);
        
        foreach($users as $index => $user) {
        
        $targetDate = $startDate->copy()->addDays($index * 5);
            $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $targetDate->toDateString(),
            ]);
          
             AttendanceEdit::factory()->create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'target_date' => $targetDate->toDateString(), 
                'request_date' => Carbon::now()->toDateString(), 
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
                'request_date' => Carbon::now()->toDateString(), 
                'new_clock_in' => $targetDate->copy()->setTime(12, 0)->toDateTimeString(), 
                'new_clock_out' => $targetDate->copy()->setTime(13, 0)->toDateTimeString(), 
                'reason' => '修正申請',
                'edited_by_admin' => false,
                'approved_at' => now(),
            ]);
        }
         
        $response = $this->actingAs($admin,'admin')->get(route('admin.stamp_correction_request.list', ['tab' => 'approved']));

        $response->assertStatus(200);
        foreach($users as $user) {
            $response->assertSee($user->name);
        }
        $edits = AttendanceEdit::where('user_id', $user->id)->get();
        foreach($edits as $edit) {
           $formattedDate = Carbon::parse($edit->target_date)->format('Y/m/d');
            $response->assertSee($formattedDate);
            $response->assertSee(Carbon::now()->format('Y/m/d'));
            $response->assertSee($edit->reason);
        }
        $breakEdits = BreakTimeEdit::where('user_id', $user->id)->get();
        foreach($breakEdits as $breakEdit) {
             $formattedDate = Carbon::parse($breakEdit->target_date)->format('Y/m/d');
            $response->assertSee($formattedDate);
            
            $response->assertSee(Carbon::now()->format('Y/m/d'));
             $response->assertSee($breakEdit->reason);
        }
    }
    public function test_admin_can_confirm_that_user_requested_attendance_edits_correctly()
    {
       $admin = User::factory()->create(['role' => 'admin']);

        $user = User::factory()->create();
        $targetDate = Carbon::create(2025, 5,1);
            $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $targetDate->toDateString(),
            ]);
          
        $attendanceEdit =  AttendanceEdit::factory()->create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'target_date' => $targetDate->toDateString(), 
                'request_date' => Carbon::create(2025,5,6)->toDateString(), 
                'new_clock_in' => $targetDate->copy()->setTime(9, 0)->toDateTimeString(), 
                'new_clock_out' => $targetDate->copy()->setTime(18, 0)->toDateTimeString(), 
                'reason' => '修正申請',
                'edited_by_admin' => false,
                'approved_at' => null,
            ]);
        
            $breakTime = BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
            
            ]);
            $breakEdit = BreakTimeEdit::factory()->create([ 
                'user_id' => $user->id,
                'break_time_id' => $breakTime->id,
                'target_date' => $targetDate->toDateString(), 
                'request_date' => Carbon::create(2025,5,6)->toDateString(), 
                'new_clock_in' => $targetDate->copy()->setTime(12, 0)->toDateTimeString(), 
                'new_clock_out' => $targetDate->copy()->setTime(13, 0)->toDateTimeString(), 
                'reason' => '修正申請',
                'edited_by_admin' => false,
                'approved_at' => null,
            ]);
        
        $response = $this->actingAs($admin,'admin')->get(route('admin.stamp_correction_request.list', ['tab' => 'waiting'])); 
        $response->assertStatus(200);

        $response->assertSee(route('admin.approvePage',['attendance_correct_request' => $attendanceEdit->id]));



        $response = $this->actingAs($admin,'admin')->get(route('admin.approvePage',['attendance_correct_request' => $attendanceEdit->id]));

        $response->assertStatus(200);
       
        $response->assertSee($user->name);
         $response->assertSee(Carbon::parse($attendanceEdit->target_date)->format('n月j日'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
       
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('修正申請');
    }
    public function test_admin_can_approve_user_requested_attendance_edits()
    {
       $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $targetDate = Carbon::create(2025, 5,1);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $targetDate->toDateString(),
            'clock_in' => $targetDate->copy()->setTime(8, 0)->toDateTimeString(), 
            'clock_out' => $targetDate->copy()->setTime(17, 0)->toDateTimeString(),
            ]);
          
        $attendanceEdit =  AttendanceEdit::factory()->create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'target_date' => $targetDate->toDateString(), 
                'request_date' => Carbon::create(2025,5,6)->toDateString(), 
                'new_clock_in' => $targetDate->copy()->setTime(9, 0)->toDateTimeString(), 
                'new_clock_out' => $targetDate->copy()->setTime(18, 0)->toDateTimeString(), 
                'reason' => '修正申請',
                'edited_by_admin' => false,
                'approved_at' => null,
            ]);
        $breakTime = BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
                'clock_in' => $targetDate->copy()->setTime(12, 0)->toDateTimeString(),
                'clock_out' => $targetDate->copy()->setTime(13, 30)->toDateTimeString(),
            ]);
        $breakEdit = BreakTimeEdit::factory()->create([ 
                'user_id' => $user->id,
                'break_time_id' => $breakTime->id,
                'target_date' => $targetDate->toDateString(), 
                'request_date' => Carbon::create(2025,5,6)->toDateString(), 
                'new_clock_in' => $targetDate->copy()->setTime(12, 30)->toDateTimeString(), 
                'new_clock_out' => $targetDate->copy()->setTime(13, 30)->toDateTimeString(), 
                'reason' => '修正申請',
                'edited_by_admin' => false,
                'approved_at' => null,
            ]);
        $response = $this->actingAs($admin,'admin')->get(route('admin.approvePage',['attendance_correct_request' => $attendanceEdit->id]));
        $response->assertStatus(200);
        $response->assertSee($user->name);
         $response->assertSee(Carbon::parse($attendanceEdit->target_date)->format('n月j日'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
       
        $response->assertSee('12:30');
        $response->assertSee('13:30');
        $response->assertSee('修正申請'); 

        $response = $this->actingAs($admin, 'admin')->post(route('admin.attendanceEdit.approve',['id' => $attendanceEdit->id]));
        

        $attendanceEdit->refresh();
        $breakEdit->refresh();
        $attendance->refresh();
        $breakTime->refresh();

        $this->assertNotNull('$attendanceEdit->approved_at');
        $this->assertNotNull($breakEdit->approved_at);

       
        $this->assertEquals($targetDate->copy()->setTime(9, 0)->toDateTimeString(), $attendance->clock_in);
        $this->assertEquals($targetDate->copy()->setTime(18, 0)->toDateTimeString(), $attendance->clock_out);
        $this->assertEquals($targetDate->copy()->setTime(12, 30)->toDateTimeString(), $breakTime->clock_in);
        $this->assertEquals($targetDate->copy()->setTime(13, 30)->toDateTimeString(), $breakTime->clock_out);
    }
}
