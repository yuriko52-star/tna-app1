<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;


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
        
        return [
            'user_id' => Arr::random([2,3]),
            'date' => $this->faker->date() ,
            'clock_in' => null ,
            'clock_out' => null ,
        ];
    }
}
