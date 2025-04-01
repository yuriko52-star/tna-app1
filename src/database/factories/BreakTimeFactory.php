<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BreakTime;
use App\Models\Attendance;
use Carbon\Carbon;
// 新しくつけた

class BreakTimeFactory extends Factory
{
    protected $model = BreakTime::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
   public function definition()
    { 
        // 前のコード
        // $attendance = Attendance::whereNotNull('clock_in')->inRandomOrder()->first();


        $attendance = Attendance::whereNotNull('clock_in')->orderby('date','asc')->inRandomOrder()->first();
        
        if(!$attendance) {
            return [
                
            ];
        }
        
        
        
        $date = Carbon::parse($attendance->date);
        $clockIn = $date->isWeekend() ? null : Carbon::parse($attendance->clock_in)->addHours(rand(2,4))->addMinutes(rand(0,5)*10);
    
        $clockOut = $clockIn ? $clockIn->copy()->addMinutes(rand(3, 6)*10) : null;
        
        return [
            'attendance_id' => $attendance->id,
            'date' => $attendance->date,
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
        ];
    }
}
