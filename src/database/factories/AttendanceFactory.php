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
        $clockIn = Carbon::createFromTime(rand(8, 9), rand(0, 59));
        $clockOut = (clone $clockIn)->addHours(rand(8,10))->addMinutes(rand(0,59));

        return [
            'user_id' => User::where('role', 'user')->pluck('id')->random(),
            'attendance_status_id' => 4,
            'date' => now()->format('Y-m-d'), //日付重複防止のためSeeder側で管理
            'clock_in' => $clockIn->format('H:i:s'),
            'clock_out' => $clockOut->format('H:i:s'),
            'remark' => null,
        ];
    }
}
