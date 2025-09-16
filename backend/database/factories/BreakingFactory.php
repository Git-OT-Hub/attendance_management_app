<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Breaking>
 */
class BreakingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dateTime = Carbon::now()->subHours(6)->setSecond(0)->format('Y-m-d H:i:s');

        return [
            'attendance_id' => Attendance::factory(),
            'start_time' => $dateTime,
            'end_time' => Carbon::parse($dateTime)->addHour(),
        ];
    }
}
