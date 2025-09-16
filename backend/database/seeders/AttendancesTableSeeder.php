<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\Breaking;
use App\Models\User;
use App\Models\AttendanceCorrection;
use App\Models\BreakingCorrection;
use Carbon\Carbon;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * 勤怠作成の期間を変更する場合
     * Carbon::create のそれぞれの引数に、勤怠データを作成したい月の開始日と終了日をそれぞれ入力して、コマンドを実行する
     * 月をまたいでの入力も可能です
     *
     * (2025/7/1 〜 2025/9/15 までの勤怠データを作成する例)
     * $startDate = Carbon::create(2025, 7, 1);
     * $endDate   = Carbon::create(2025, 9, 15);
     *
     * コマンド：(必要に応じて実行してください)
     * php artisan migrate:fresh (既にDBにデータが存在する場合は実行してください)
     * php artisan db:seed
     */
    public function run(): void
    {
        $users = User::all();

        // 勤怠作成
        foreach ($users as $user) {
            // 開始日
            $startDate = Carbon::create(2025, 7, 1);
            // 終了日
            $endDate   = Carbon::create(2025, 9, 15);

            // 1日ごとにループ
            for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                // ランダムで休暇を作成(4日に1回ぐらい)
                if (rand(1, 3) === 1) {
                    continue;
                }

                // 勤怠データ作成
                $attendance = Attendance::create([
                    'user_id'             => $user->id,
                    'start_date'          => $date->toDateString(),
                    'start_time'          => $date->copy()->setTime(9, 0, 0),
                    'end_time'            => $date->copy()->setTime(18, 0, 0),
                    'total_breaking_time' => 3600, // 1時間休憩
                    'actual_working_time' => 28800, // 8時間勤務
                    'state'               => 3, // 退勤済
                ]);

                // 休憩データ作成
                Breaking::create([
                    'attendance_id' => $attendance->id,
                    'start_time'    => $date->copy()->setTime(12, 0, 0),
                    'end_time'      => $date->copy()->setTime(13, 0, 0),
                ]);
            }
        }

        // ランダムで勤怠修正申請を作成
        foreach ($users as $user) {
            $attendances = $user->attendances;

            foreach ($attendances as $attendance) {
                if (rand(1, 5) === 1) {
                    $attendance->update([
                        'correction_request_date' => Carbon::now()->subMinute(rand(1, 60)),
                    ]);
                    $attendance = $attendance->fresh();

                    $attendanceCorrection = AttendanceCorrection::create([
                        'attendance_id' => $attendance->id,
                        'start_date' => $attendance->start_date,
                        'start_time' => Carbon::parse($attendance->start_time)->addHour(),
                        'end_time' => Carbon::parse($attendance->end_time)->addHour(2),
                        'total_breaking_time' => 5400, // 1時間30分休憩
                        'actual_working_time' => 30600, // 8時間30分勤務
                        'comment' => '遅延のため',
                        'correction_request_date' => $attendance->correction_request_date,
                        'state' => 3,
                    ]);

                    BreakingCorrection::create([
                        'attendance_correction_id' => $attendanceCorrection->id,
                        'start_time' => Carbon::parse($attendanceCorrection->start_time)->addHour(3),
                        'end_time' => Carbon::parse($attendanceCorrection->start_time)->addHour(4),
                    ]);

                    BreakingCorrection::create([
                        'attendance_correction_id' => $attendanceCorrection->id,
                        'start_time' => Carbon::parse($attendanceCorrection->start_time)->addHour(7),
                        'end_time' => Carbon::parse($attendanceCorrection->start_time)->addHour(7)->addMinutes(30),
                    ]);
                }
            }
        }

        // ランダムで勤怠修正申請を承認
        foreach ($users as $user) {
            $attendances = $user->attendances()->whereNotNull('correction_request_date')->get();

            foreach ($attendances as $attendance) {
                if (rand(1, 3) === 1) {
                    $attendance->update([
                        'start_time' => Carbon::parse($attendance->start_time)->addHour(),
                        'end_time' => Carbon::parse($attendance->end_time)->addHour(2),
                        'total_breaking_time' => 5400,
                        'actual_working_time' => 30600,
                        'correction_request_date' => null,
                        'is_approved_history' => true,
                    ]);
                    $attendance = $attendance->fresh();

                    $attendance->breakings()->delete();
                    Breaking::create([
                        'attendance_id' => $attendance->id,
                        'start_time' => Carbon::parse($attendance->start_time)->addHour(3),
                        'end_time' => Carbon::parse($attendance->start_time)->addHour(4),
                    ]);
                    Breaking::create([
                        'attendance_id' => $attendance->id,
                        'start_time' => Carbon::parse($attendance->start_time)->addHour(7),
                        'end_time' => Carbon::parse($attendance->start_time)->addHour(7)->addMinutes(30),
                    ]);

                    $attendanceCorrection = $attendance->attendanceCorrections()->whereNull('approval_date')->first();
                    $attendanceCorrection->update([
                        'approval_date' => Carbon::parse($attendanceCorrection->correction_request_date)->addHour(),
                    ]);
                }
            }
        }
    }
}
