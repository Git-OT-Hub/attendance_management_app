<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\Breaking;
use App\Models\User;
use Carbon\Carbon;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Carbon::create のそれぞれの引数に、勤怠データを作成したい月の開始日と終了日をそれぞれ入力して、seederコマンドを実行する
     * 月をまたいでの入力も可能です
     * 月の途中まで勤怠データを作成する場合は下記のように記述(2025/8/17までの勤怠データを作成する例)
     * $startDate = Carbon::create(2025, 8, 1);
     * $endDate   = Carbon::create(2025, 8, 17);
     *
     * コマンド：php artisan db:seed --class=AttendancesTableSeeder
     */
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();
        // $user = User::find(5);

        // 開始日
        $startDate = Carbon::create(2025, 9, 1);
        // 終了日
        $endDate   = Carbon::create(2025, 9, 6);

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
        };
    }
}
