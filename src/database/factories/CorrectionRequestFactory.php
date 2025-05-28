<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class CorrectionRequestFactory extends Factory
{
    protected $model = CorrectionRequest::class;

    public function definition()
    {
        return [
            'user_id' => User::where('role', 'user')->pluck('id')->random(),
            'attendance_id' => Attendance::factory(),
            'clock_in' => $this->faker->time('H:i:s'),
            'clock_out' => $this->faker->time('H:i:s'),
            'remark' => $this->faker->sentence(),
            'status' => 'pending'
        ];
    }

    public function approved()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'approved'
            ];
        });
    }
}
