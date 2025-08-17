<?php

namespace App\Services\Implementations;

use Carbon\Carbon;
use App\Services\Contracts\AttendanceServiceInterface;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Models\Attendance;
use App\Http\Requests\Attendance\WorkRequest;
use App\Http\Requests\Attendance\BreakingRequest;
use App\Http\Requests\Attendance\FinishBreakingRequest;
use App\Http\Requests\Attendance\FinishWorkRequest;

class AttendanceService implements AttendanceServiceInterface
{
    private AttendanceRepositoryInterface $attendanceRepository;

    public function __construct(AttendanceRepositoryInterface $attendanceRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
    }

    /**
     * 勤務状態を確認し、その結果をAttendanceインスタンス、もしくは null で返す
     *
     * @param string $startTime
     * @return \App\Models\Attendance|null
     */
    public function workingState(string $startTime): Attendance|null
    {
        $attendance = $this->attendanceRepository->checkWorkingState($startTime);

        // 勤怠状態の日本語化
        if ($attendance) {
            $attendance->state = $attendance->convertAttendanceState();
        }

        return $attendance;
    }

    /**
     * 出勤処理を行い、その結果をAttendanceインスタンス、もしくは null で返す
     *
     * @param \App\Http\Requests\Attendance\WorkRequest $request
     * @return \App\Models\Attendance|null
     */
    public function startWorking(WorkRequest $request): Attendance|null
    {
        $attendance = $this->attendanceRepository->createAttendanceDataTime($request);

        // 勤怠状態の日本語化
        if ($attendance) {
            $attendance->state = $attendance->convertAttendanceState();
        }

        return $attendance;
    }

    /**
     * 休憩開始処理を行い、その結果を連想配列、もしくは null で返す
     *
     * @param \App\Http\Requests\Attendance\BreakingRequest $request
     * @return array{breaking_id: int, state: string}|null
     */
    public function startBreak(BreakingRequest $request): array|null
    {
        $res = null;
        $attendanceAndBreaking = $this->attendanceRepository->createStartBreak($request);

        // レスポンスデータ作成
        if ($attendanceAndBreaking) {
            // 勤怠状態の日本語化
            $attendanceState = $attendanceAndBreaking['attendance']->convertAttendanceState();

            $res = [
                'breaking_id' => $attendanceAndBreaking['breaking']->id,
                'state' => $attendanceState,
            ];
        }

        return $res;
    }

    /**
     * 休憩終了処理を行い、その結果を連想配列、もしくは null で返す
     *
     * @param \App\Http\Requests\Attendance\FinishBreakingRequest $request
     * @return array{state: string}|null
     */
    public function breakEnd(FinishBreakingRequest $request): array|null
    {
        $res = null;
        $attendance = $this->attendanceRepository->updateBreakEnd($request);

        // レスポンスデータ作成
        if ($attendance) {
            // 勤怠状態の日本語化
            $attendanceState = $attendance->convertAttendanceState();

            $res = [
                'state' => $attendanceState,
            ];
        }

        return $res;
    }

    /**
     * 退勤処理を行い、その結果を連想配列、もしくは null で返す
     *
     * @param \App\Http\Requests\Attendance\FinishWorkRequest $request
     * @return array{state: string}|null
     */
    public function clockOut(FinishWorkRequest $request): array|null
    {
        $res = null;
        $attendance = $this->attendanceRepository->updateClockOut($request);

        // レスポンスデータ作成
        if ($attendance) {
            // 勤怠状態の日本語化
            $attendanceState = $attendance->convertAttendanceState();

            $res = [
                'state' => $attendanceState,
            ];
        }

        return $res;
    }

    /**
     * 対象月の日付リストを生成し、その結果を連想配列、もしくは null で返す
     *
     * @param string $date
     * @return array<int, array{
     * date: string,
     * id: int|null,
     * start_time: string|null,
     * end_time: string|null,
     * total_breaking_time: int|null,
     * actual_working_time: int|null
     * }>|null
     */
    public function attendanceList(string $date): array|null
    {
        $attendances = $this->attendanceRepository->findAttendanceList($date);

        // レスポンスデータ作成
        if ($attendances) {
            // 日付ごとに配列を作る
            $res = [];
            $startOfMonth = Carbon::parse($date)->startOfMonth();
            $endOfMonth = Carbon::parse($date)->endOfMonth();

            for ($dateTime = $startOfMonth; $dateTime->lte($endOfMonth); $dateTime->addDay()) {
                $dateStr = $dateTime->toDateString();
                $attendance = $attendances->get($dateStr);

                $res[] = [
                    'date' => $dateTime->format('m/d') . '(' . $this->getDayOfWeek($dateTime) . ')',
                    'id' => $attendance?->id,
                    'start_time' => $attendance?->start_time ? Carbon::parse($attendance->start_time)->format('H:i') : null,
                    'end_time' => $attendance?->end_time ? Carbon::parse($attendance->end_time)->format('H:i') : null,
                    'total_breaking_time' => $this->formatSecondsToHoursMinutes($attendance?->total_breaking_time),
                    'actual_working_time' => $this->formatSecondsToHoursMinutes($attendance?->actual_working_time),
                ];
            }

            $attendances = $res;
        }

        return $attendances;
    }

    /**
     * 曜日を日本語で返す
     *
     * @param Carbon $dateTime
     * @return string
     */
    private function getDayOfWeek(Carbon $dateTime): string
    {
        $days = ['日', '月', '火', '水', '木', '金', '土'];
        return $days[$dateTime->dayOfWeek];
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
}