<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dateTime = Carbon::now()->subHours(9)->setSecond(0)->format('Y-m-d H:i:s');

        return [
            'user_id' => User::factory(),
            'start_date' => Carbon::parse($dateTime)->toDateString(),
            'start_time' => $dateTime,
            'end_time' => Carbon::parse($dateTime)->addHour(9),
            'total_breaking_time' => 3600,
            'actual_working_time' => 28800,
            'correction_request_date' => null,
            'is_approved_history' => null,
            'state' => 3,
        ];
    }
}
