<?php

namespace App\Services\Implementations\Admin;

use Carbon\Carbon;
use App\Services\Contracts\Admin\AttendanceServiceInterface;
use App\Repositories\Contracts\Admin\AttendanceRepositoryInterface;
use App\Http\Requests\Admin\Attendance\AttendanceCreateRequest;
use App\Http\Requests\Admin\Attendance\AttendanceCorrectionRequest;

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

    /**
     * 一般ユーザーの勤怠における詳細情報を取得し、その結果を連想配列、もしくは null で返す
     *
     * @param string $id
     * @return array{
     *   user_name: string,
     *   attendance_id: int,
     *   attendance_start_date: string,
     *   attendance_start_time: string,
     *   attendance_end_time: string|null,
     *   attendance_correction_request_date: string|null,
     *   comment?: string,
     *   breakings: array<string, array{
     *     breaking_id: int,
     *     breaking_start_time: string,
     *     breaking_end_time: string|null,
     *   }>|null
     * }|null
     */
    public function attendanceShow(string $id): array|null
    {
        $res = $this->attendanceRepository->findAttendanceShow($id);

        if (!$res) {
            return null;
        }

        $attendanceData = $res['attendance'];
        $breakings = $res['breakings'];
        $user = $res['user'];
        $resBreakings = [];

        // 休憩データの加工
        foreach ($breakings as $idx => $breaking) {
            $key = $idx === 0 ? '休憩' : '休憩' . ($idx + 1);

            $resBreakings[$key] = [
                'breaking_id'         => $breaking->id,
                'breaking_start_time' => $breaking->start_time
                    ? $breaking->start_time
                    : null,
                'breaking_end_time'   => $breaking->end_time
                    ? $breaking->end_time
                    : null,
            ];
        }

        if ($attendanceData->correction_request_date) {
            // 勤怠修正依頼を行い、承認待ちの場合

            // 休憩データ無しの場合、空枠を１つ追加
            if (count($breakings) === 0) {
                $nextKey = '休憩';
                $resBreakings[$nextKey] = [];
            }

            return [
                'user_name'             => $user->name,
                'attendance_id'         => $attendanceData->id,
                'attendance_start_date' => $attendanceData->start_date,
                'attendance_start_time' => $attendanceData->start_time
                    ? $attendanceData->start_time
                    : null,
                'attendance_end_time'   => $attendanceData->end_time
                    ? $attendanceData->end_time
                    : null,
                'attendance_correction_request_date' => $attendanceData->correction_request_date
                    ? $attendanceData->correction_request_date
                    : null,
                'comment' => $attendanceData->comment,
                'breakings'             => $resBreakings,
            ];
        } else {
            // 勤怠修正依頼なしの場合

            // 休憩データの数 +1 の空枠を追加
            $nextKey = count($breakings) === 0 ? '休憩' : '休憩' . (count($breakings) + 1);
            $resBreakings[$nextKey] = [];

            return [
                'user_name'             => $user->name,
                'attendance_id'         => $attendanceData->id,
                'attendance_start_date' => $attendanceData->start_date,
                'attendance_start_time' => $attendanceData->start_time
                    ? $attendanceData->start_time
                    : null,
                'attendance_end_time'   => $attendanceData->end_time
                    ? $attendanceData->end_time
                    : null,
                'attendance_correction_request_date' => $attendanceData->correction_request_date
                    ? $attendanceData->correction_request_date
                    : null,
                'breakings'             => $resBreakings,
            ];
        }
    }

    /**
     * 勤怠修正処理を行い、その結果を連想配列、もしくは null で返す
     *
     * @param AttendanceCorrectionRequest $request
     * @return array{
     *   user_name: string,
     *   attendance_id: int,
     *   attendance_start_date: string,
     *   attendance_start_time: string,
     *   attendance_end_time: string,
     *   attendance_correction_request_date: string,
     *   breakings: array<string, array{
     *     breaking_id: int,
     *     breaking_start_time: string,
     *     breaking_end_time: string,
     *   }>|null
     * }|null
     */
    public function correctAttendance(AttendanceCorrectionRequest $request): array|null
    {
        $res = $this->attendanceRepository->updateAttendanceCorrection($request);

        if (!$res) {
            return null;
        }

        $attendanceData = $res['attendance'];
        $breakings = $res['breakings'];
        $user = $res['user'];
        $resBreakings = [];

        // 休憩データの加工
        foreach ($breakings as $idx => $breaking) {
            $key = $idx === 0 ? '休憩' : '休憩' . ($idx + 1);

            $resBreakings[$key] = [
                'breaking_id'         => $breaking->id,
                'breaking_start_time' => $breaking->start_time
                    ? $breaking->start_time
                    : null,
                'breaking_end_time'   => $breaking->end_time
                    ? $breaking->end_time
                    : null,
            ];
        }

        // 休憩データの数 +1 の空枠を追加
        $nextKey = count($breakings) === 0 ? '休憩' : '休憩' . (count($breakings) + 1);
        $resBreakings[$nextKey] = [];

        return [
            'user_name'             => $user->name,
            'attendance_id'         => $attendanceData->id,
            'attendance_start_date' => $attendanceData->start_date,
            'attendance_start_time' => $attendanceData->start_time
                ? $attendanceData->start_time
                : null,
            'attendance_end_time'   => $attendanceData->end_time
                ? $attendanceData->end_time
                : null,
            'attendance_correction_request_date' => $attendanceData->correction_request_date
                ? $attendanceData->correction_request_date
                : null,
            'breakings'             => $resBreakings,
        ];
    }
}