<?php

namespace App\Services\Implementations\Admin;

use Carbon\Carbon;
use App\Services\Contracts\Admin\AttendanceServiceInterface;
use App\Repositories\Contracts\Admin\AttendanceRepositoryInterface;
use App\Http\Requests\Admin\Attendance\AttendanceCreateRequest;

class AttendanceService implements AttendanceServiceInterface
{
    private AttendanceRepositoryInterface $attendanceRepository;

    public function __construct(AttendanceRepositoryInterface $attendanceRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
    }

    /**
     * 対象日の一般ユーザー勤怠情報リストを生成し、その結果を連想配列、もしくは null で返す
     *
     * @param string $date
     * @return array<int, array{
     *   id: int|null,
     *   start_time: string|null,
     *   end_time: string|null,
     *   total_breaking_time: int|null,
     *   actual_working_time: int|null,
     *   user_name: string,
     *   user_id: int,
     * }>|null
     */
    public function attendanceTodayList(string $date): array|null
    {
        $users = $this->attendanceRepository->findAttendanceTodayList($date);

        if (!$users) {
            return null;
        }

        $dateOnly = Carbon::parse($date)->toDateString();
        $res = [];

        foreach ($users as $user) {
            // 対象日の勤怠データを１件取得
            $attendance = $user->attendances()
                ->whereDate('start_date', '=', $dateOnly)
                ->first();

            // デフォルトは attendance
            $target = $attendance;

            // 修正依頼中であれば、修正依頼中のデータを使用
            if ($attendance && $attendance->correction_request_date) {
                $correction = $attendance->attendanceCorrections()
                    ->whereNull('approval_date')
                    ->orderByDesc('id')
                    ->first();

                if ($correction) {
                    $target = $correction;
                }
            }

            $res[] = [
                'id' => $attendance?->id,
                'start_time' => $target?->start_time ? Carbon::parse($target->start_time)->format('H:i') : null,
                'end_time' => $target?->end_time ? Carbon::parse($target->end_time)->format('H:i') : null,
                'total_breaking_time' => $this->formatSecondsToHoursMinutes($target?->total_breaking_time),
                'actual_working_time' => $this->formatSecondsToHoursMinutes($target?->actual_working_time),
                'user_name' => $user->name,
                'user_id' => $user->id,
            ];
        }

        return $res;
    }

    /**
     * 秒数を「H:i」形式の文字列に変換する
     *
     * @param int|null $seconds
     * @return string|null
     */
    private function formatSecondsToHoursMinutes(?int $seconds): ?string
    {
        if ($seconds === null) {
            return null;
        }

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        return sprintf('%d:%02d', $hours, $minutes);
    }

    /**
     * 勤怠新規登録を行い、その結果を 整数 もしくは null で返す
     *
     * @param AttendanceCreateRequest $request
     * @return int|null
     */
    public function createAttendance(AttendanceCreateRequest $request): int|null
    {
        $attendance = $this->attendanceRepository->createAttendanceRecords($request);

        if (!$attendance) {
            return null;
        }

        return $attendance->id;
    }
}