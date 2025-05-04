<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
// use Carbon\Carbon;

class AttendanceFactory extends Factory
{

    protected $model = Attendance::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        /*$month = rand(2,3);
        $date = Carbon::create(2025,$month,rand(1,Carbon::create(2025,$month,1)->daysInMonth));
        順番がバラバラになるのでガラリと変更した
        if($date->isWeekend()) {
            $clockIn = null;
            $clockOut = null;
            }else {
            $clockIn = $date->copy()->addHours(rand(8,9))->addMinutes(rand(0,5)*10);
            $clockOut = $clockIn->copy()->addHours(rand(7,9))->addMinutes(rand(0,5)*10); 
            }
        */
        return [
             'user_id' => Arr::random([2,3]),
            // 'user_id' => fn() => User::factory(),
             'date' => $this->faker->date() ,
            // 'date' => now()->toDateString(),
            'clock_in' => null ,
            'clock_out' => null ,
        ];
    }
}
