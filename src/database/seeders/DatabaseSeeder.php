<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        //  \App\Models\User::factory(10)->create();
                    // $this->call(UsersTableSeeder::class);
         
         
        // ここから勤務、退勤時間
         $users = [2,3];

        foreach($users as $user) {
            $dates = collect(range(0,59))->map(fn ($i) => Carbon::create(2025,2,1)->addDays($i));

            foreach($dates as $date) {
                if($date->isWeekend()) {
                   $clockIn = null;
                    $clockOut = null;
                } else {
                    $clockIn = $date->copy()->addHours(rand(8, 9))->addMinutes(rand(0, 5) * 10);
                    $clockOut = $date->copy()->addHours(rand(17, 19))->addMinutes(rand(0, 5) * 10);
                } 
                
                $attendance =Attendance::factory()->create([
                    'user_id' => $user,
                    'date' => $date->toDateString(),
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                ]);
        // ここから休憩時間
                
                
                if ($date->isWeekend()) {
                    BreakTime::factory()->create([
                    'attendance_id' => $attendance->id,
                    // 'date' => $attendance->date,
                    'clock_in' => null,
                    'clock_out' => null,
                    ]);
                    continue; 
                }

                 $previousBreakClockOut = null; // 直前の休憩終了時間を保存

                $takeFirstBreak = rand(0, 1); // 0: 休憩なし, 1: 休憩あり
                if ($takeFirstBreak) {
                        $breakClockIn = $date->copy()->setTime(10, 0);
                        $breakClockOut = $date->copy()->setTime(10, 15);
            
                        BreakTime::factory()->create([
                            'attendance_id' => $attendance->id,
                            // 'date' => $attendance->date,
                            'clock_in' => $breakClockIn,
                            'clock_out' => $breakClockOut,
                            ]);

                        $previousBreakClockOut = $breakClockOut;
                    }
            
        
                $lunchStartMinutes = rand(0, 5) * 10; // 0, 10, 20, 30, 40, 50 のいずれか
                $breakClockIn = $date->copy()->setTime(12, $lunchStartMinutes);
                $breakClockOut = $breakClockIn->copy()->addMinutes(50);

                BreakTime::factory()->create([
                    'attendance_id' => $attendance->id,
                    // 'date' => $attendance->date,
                    'clock_in' => $breakClockIn,
                    'clock_out' => $breakClockOut,
                    ]);
                
            }
        
        }
      
    }
    
} 

    
