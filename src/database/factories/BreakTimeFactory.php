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
        // >inRandomOrder()の（）を入れ忘れてエラーになった
        if(!$attendance) {
            return [
                
            ];
        }
        // clock_in を Carbon インスタンスに変換
        // ２番目のコード。日時がずれる$clockIn = Carbon::parse($attendance->clock_in)->copy()->addHours(rand(2, 4))->addMinutes(rand(0, 5) * 10);
        // parse($attendance->clock_in)->copy()->のcopy()->がダメでした。
        // １番目のコード$clockIn = $attendance->clock_in->copy()->addHours(rand(2,4))->addMinutes(rand(0,5)*10);
        // "Call to a member function copy() on string" の原因は、$attendance->clock_in が Carbon インスタンスではなく、文字列（string）になっている ため。$attendance->clock_in を Carbon インスタンスとして扱うために、Carbon::parse() を使って明示的に変換。
        $date = Carbon::parse($attendance->date);
        $clockIn = $date->isWeekend() ? null : Carbon::parse($attendance->clock_in)->addHours(rand(2,4))->addMinutes(rand(0,5)*10);
        // 下のコードも見直したい30－60分の時間差ありすぎ。せめて30－50分？
        $clockOut = $clockIn ? $clockIn->copy()->addMinutes(rand(3, 6)*10) : null;
        //  $clockOut = $date->isWeekend() ? null : $clockIn->copy()->addMinutes(rand(1,6)*10);
        // $attendance->date が string（日付の文字列） であるため、 isWeekend() メソッドを直接使用できない。
        // Laravel の isWeekend() メソッドは Carbon インスタンス に対してのみ使用可能なので、 Carbon に変換 する必要がある。
        // 次のコードは書くのを見送った$clockOut = $clockIn->copy()->addMinutes(rand(10, 30)); // 10～30分後に終了
        return [
            'attendance_id' => $attendance->id,
            'date' => $attendance->date,
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
        ];
    }
}
