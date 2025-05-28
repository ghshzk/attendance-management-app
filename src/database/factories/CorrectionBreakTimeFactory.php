<?php

namespace Database\Factories;

use App\Models\CorrectionRequest;
use App\Models\CorrectionBreakTime;
use Illuminate\Database\Eloquent\Factories\Factory;

class CorrectionBreakTimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'correction_request_id' => CorrectionRequest::factory(),
            'break_start' => $this->faker->time('H:i:s'),
            'break_end' => $this->faker->time('H:i:s'),
        ];
    }
}
