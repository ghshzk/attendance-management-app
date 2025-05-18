<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use Illuminate\Support\Carbon;

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
        $workDate = $this->faker->dateTimeBetween('-1 month', 'now');
        $clockIn = Carbon::instance($workDate)->setTime(rand(8, 9), rand(0, 59));
        $clockOut = (clone $clockIn)->addHours(rand(8,10))->addMinutes(rand(0,59));

        return [
            'user_id' => User::where('role', 'user')->pluck('id')->random(),
            'attendance_status_id' => 4,
            'date' => $workDate->format('Y-m-d'),
            'clock_in' => $clockIn->format('H:i:s'),
            'clock_out' => $clockOut->format('H:i:s'),
            'remark' => $this->faker->optional()->sentence,
        ];
    }
}
