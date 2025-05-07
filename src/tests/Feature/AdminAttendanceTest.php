<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
Use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
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
}
