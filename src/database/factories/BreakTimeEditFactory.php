<?php

namespace Database\Factories;


use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\BreakTime;
use App\Models\BreakTimeEdit;
use Carbon\Carbon;

class BreakTimeEditFactory extends Factory
{
    protected $model = BreakTimeEdit::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'break_time_id' => BreakTime::factory(),
            'user_id' => User::factory(),
            'request_date' => Carbon::now()->toDateString(),
            'target_date' => Carbon::now()->subDays(rand(1, 10))->toDateString(),
            'new_clock_in' => Carbon::createFromFormat('Y-m-d H:i', '2025-05-01 12:00')->toDateTimeString(),
            'new_clock_out' => Carbon::createFromFormat('Y-m-d H:i', '2025-05-01 13:00')->toDateTimeString(),
            'reason' => $this->faker->sentence,
            'approved_at' => null,
            'edited_by_admin' => false,
        ];
    }
}
