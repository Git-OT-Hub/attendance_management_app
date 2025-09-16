<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\Breaking;

class AttendanceControllerTest extends TestCase
{
    /**
     * ステータス確認機能
     */
    public function test_attendance_勤務外の場合、「勤務外」が返される(): void
    {
        $user = $this->login();
        $startTime = Carbon::now()->setSecond(0)->format('Y-m-d H:i:s');

        $response = $this->getJson("/api/attendance/state?start_time=" . urlencode($startTime));

        $response->assertOk()
            ->assertJson([
                'state' => '勤務外',
            ]);
    }

    /**
     * ステータス確認機能
     */
    public function test_attendance_出勤中の場合、「出勤中」が返される(): void
    {
        $user = $this->login();
        $startTime = Carbon::now()->setSecond(0)->format('Y-m-d H:i:s');
        $work = 1;
        $payload = [
            'start_time' => $startTime,
            'state' => $work,
        ];

        $response = $this->postJson('/api/attendance/work', $payload);

        $response->assertCreated()
            ->assertJson([
                'state' => '出勤中',
                'user_id' => $user->id,
                'start_date' => Carbon::parse($startTime)->toDateString(),
                'start_time' => $startTime,
            ]);
    }

    /**
     * ステータス確認機能
     */
    public function test_attendance_休憩中の場合、「休憩中」が返される(): void
    {
        // 出勤処理
        $user = $this->login();
        $dateTime = Carbon::now()->subHours(9)->setSecond(0)->format('Y-m-d H:i:s');
        $work = 1;
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_date' => Carbon::parse($dateTime)->toDateString(),
            'start_time' => $dateTime,
            'end_time' => null,
            'total_breaking_time' => null,
            'actual_working_time' => null,
            'correction_request_date' => null,
            'is_approved_history' => null,
            'state' => $work,
        ]);

        // 休憩処理
        $break = 2;
        $startTime = Carbon::parse($dateTime)->addHour(3)->format('Y-m-d H:i:s');
        $payload = [
            'attendance_id' => $attendance->id,
            'start_time' => $startTime,
            'state' => $break,
        ];
        $response = $this->postJson('/api/attendance/break', $payload);

        $response->assertCreated()
            ->assertJson([
                'state' => '休憩中',
            ]);
    }

    /**
     * ステータス確認機能
     */
    public function test_attendance_退勤済の場合、「退勤済」が返される(): void
    {
        // 出勤処理
        $user = $this->login();
        $dateTime = Carbon::now()->subHours(9)->setSecond(0)->format('Y-m-d H:i:s');
        $work = 1;
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_date' => Carbon::parse($dateTime)->toDateString(),
            'start_time' => $dateTime,
            'end_time' => null,
            'total_breaking_time' => null,
            'actual_working_time' => null,
            'correction_request_date' => null,
            'is_approved_history' => null,
            'state' => $work,
        ]);

        // 退勤処理
        $finished = 3;
        $endTime = Carbon::parse($dateTime)->addHour(9)->format('Y-m-d H:i:s');
        $payload = [
            'attendance_id' => $attendance->id,
            'end_time' => $endTime,
            'state' => $finished,
        ];
        $response = $this->patchJson('/api/attendance/finish_work', $payload);

        $response->assertOk()
            ->assertJson([
                'state' => '退勤済',
            ]);
    }

    /**
     * 勤怠一覧情報取得機能のテスト
     */
    public function test_attendance_勤怠一覧を取得した際、登録した出勤時刻、休憩時刻、退勤時刻の値が返される(): void
    {
        $user = $this->login();
        $finished = 3;
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_date' => '2025-09-01',
            'start_time' => '2025-09-01 09:00:00',
            'end_time' => '2025-09-01 18:00:00',
            'total_breaking_time' => 3600,
            'actual_working_time' => 28800,
            'correction_request_date' => null,
            'is_approved_history' => null,
            'state' => $finished,
        ]);
        $attendance2 = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_date' => '2025-09-03',
            'start_time' => '2025-09-03 10:00:00',
            'end_time' => '2025-09-03 20:00:00',
            'total_breaking_time' => 5400,
            'actual_working_time' => 30600,
            'correction_request_date' => null,
            'is_approved_history' => null,
            'state' => $finished,
        ]);

        $month = '2025-09';
        $response = $this->getJson("/api/attendance/list?month=" . $month);

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $attendance->id,
                'date' => '09/01(月)',
                'start_time' => '09:00',
                'end_time' => '18:00',
                'total_breaking_time' => '1:00',
                'actual_working_time' => '8:00',
                'year_month' => '2025-09-01',
            ])
            ->assertJsonFragment([
                'id' => $attendance2->id,
                'date' => '09/03(水)',
                'start_time' => '10:00',
                'end_time' => '20:00',
                'total_breaking_time' => '1:30',
                'actual_working_time' => '8:30',
                'year_month' => '2025-09-03',
            ]);
    }

    /**
     * 勤怠詳細情報取得機能のテスト
     */
    public function test_attendance_勤怠詳細を取得した際、登録した出勤時刻、休憩時刻、退勤時刻の値が返される(): void
    {
        $user = $this->login();
        $finished = 3;
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_date' => '2025-09-01',
            'start_time' => '2025-09-01 09:00:00',
            'end_time' => '2025-09-01 18:00:00',
            'total_breaking_time' => 3600,
            'actual_working_time' => 28800,
            'correction_request_date' => null,
            'is_approved_history' => null,
            'state' => $finished,
        ]);
        $breaking = Breaking::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time' => '2025-09-01 12:00:00',
            'end_time' => '2025-09-01 13:00:00',
        ]);

        $response = $this->getJson("/api/attendance/" . (string)$attendance->id);

        $response->assertOk()
            ->assertJson([
                'user_name' => $user->name,
                'attendance_id' => $attendance->id,
                'attendance_start_date' => '2025-09-01',
                'attendance_start_time' => '2025-09-01 09:00:00',
                'attendance_end_time' => '2025-09-01 18:00:00',
                'attendance_correction_request_date' => null,
                'breakings' => [
                    '休憩' => [
                        'breaking_id' => $breaking->id,
                        'breaking_start_time' => '2025-09-01 12:00:00',
                        'breaking_end_time' => '2025-09-01 13:00:00',
                    ],
                ],
            ]);
    }
}
