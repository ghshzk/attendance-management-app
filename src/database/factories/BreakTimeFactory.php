<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        return [
            //
        ];
    }

    public function forAttendance(Attendance $attendance)
    {
        return $this->state(function (array $attributes) use ($attendance) {
            $breakStart = Carbon::parse($attendance->date)->setTime(rand(12,14), rand(0,59));
            $breakEnd = (clone $breakStart)->addMinutes(rand(50, 60)); //50~60分程度の休憩

            return [
                'attendance_id' => $attendance->id,
                'break_start' => $breakStart->format('H:i:s'),
                'break_end' => $breakEnd->format('H:i:s'),
            ];
        });
    }
}
