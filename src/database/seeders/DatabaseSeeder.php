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
        // \App\Models\User::factory(10)->create();
                   $this->call(UsersTableSeeder::class);
         
         
        // ここから修正バージョン
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
                 for ($i = 0; $i < rand(1, 2); $i++) {
                    $breakClockIn = $clockIn ? $clockIn->copy()->addHours(rand(2,4))->addMinutes(rand(0,5)*10) : null; 
                    $breakClockOut = $breakClockIn ? $breakClockIn->copy()->addMinutes(rand(3, 6)*10) : null;
                    BreakTime::factory()->create([
                        'attendance_id' => $attendance->id,
                        'date' => $attendance->date,
                        'clock_in' => $breakClockIn,
                        'clock_out' => $breakClockOut,
                    ]);
                 }
                //  問題点、一日複数休憩を取る場合、1回目と2回目がダブっているのが気になった。
                // 例）１回目　10：30～11：00　2回目　10:40~11:20
                // だぶらないように設定したいように設定したい。例えば1回目は10：00～10：20　で　2回目は12：30～13：20など。明日やろう
                
                
            }
        
        }
      
    }
} 