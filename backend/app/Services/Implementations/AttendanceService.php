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
use App\Http\Requests\Attendance\AttendanceCorrectionRequest;
use App\Http\Requests\Attendance\AttendanceCreateRequest;

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
     * actual_working_time: int|null,
     * year_month: string
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

                // デフォルトは attendance
                $target = $attendance;

                if ($attendance && $attendance->correction_request_date) {
                    $correction = $attendance->attendanceCorrections->first();
                    if ($correction) {
                        $target = $correction;
                    }
                }

                $res[] = [
                    'date' => $dateTime->format('m/d') . '(' . $this->getDayOfWeek($dateTime) . ')',
                    'id' => $attendance?->id,
                    'start_time' => $target?->start_time ? Carbon::parse($target->start_time)->format('H:i') : null,
                    'end_time' => $target?->end_time ? Carbon::parse($target->end_time)->format('H:i') : null,
                    'total_breaking_time' => $this->formatSecondsToHoursMinutes($target?->total_breaking_time),
                    'actual_working_time' => $this->formatSecondsToHoursMinutes($target?->actual_working_time),
                    'year_month' => $dateTime->format('Y-m-d'),
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

    /**
     * ログインユーザーの勤怠における詳細情報を取得し、その結果を連想配列、もしくは null で返す
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
     * ログインユーザーの勤怠修正履歴における詳細情報を取得し、その結果を連想配列、もしくは null で返す
     *
     * @param string $id
     * @return array{
     *   user_name: string,
     *   start_date: string,
     *   start_time: string,
     *   end_time: string,
     *   comment: string,
     *   breakings: array<string, array{
     *     start_time: string,
     *     end_time: string,
     *   }>|null
     * }|null
     */
    public function attendanceCorrectionShow(string $id): array|null
    {
        $res = $this->attendanceRepository->findAttendanceCorrectionShow($id);

        if (!$res) {
            return null;
        }

        $attendanceCorrectionData = $res['attendance_correction'];
        $breakingCorrections = $res['breaking_corrections'];
        $user = $res['user'];
        $resBreakingCorrections = [];

        // 休憩データの加工
        foreach ($breakingCorrections as $idx => $breaking) {
            $key = $idx === 0 ? '休憩' : '休憩' . ($idx + 1);

            $resBreakingCorrections[$key] = [
                'start_time' => $breaking->start_time
                    ? $breaking->start_time
                    : null,
                'end_time'   => $breaking->end_time
                    ? $breaking->end_time
                    : null,
            ];
        }

        // 休憩データの数 +1 の空枠を追加
        $nextKey = count($breakingCorrections) === 0 ? '休憩' : '休憩' . (count($breakingCorrections) + 1);
        $resBreakingCorrections[$nextKey] = [];

        return [
            'user_name'  => $user->name,
            'start_date' => $attendanceCorrectionData->start_date
                ? $attendanceCorrectionData->start_date
                : null,
            'start_time' => $attendanceCorrectionData->start_time
                ? $attendanceCorrectionData->start_time
                : null,
            'end_time'   => $attendanceCorrectionData->end_time
                ? $attendanceCorrectionData->end_time
                : null,
            'comment'    => $attendanceCorrectionData->comment
                ? $attendanceCorrectionData->comment
                : null,
            'breakings'  => $resBreakingCorrections,
        ];
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
     *   comment: string,
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
     * 承認待ち申請一覧を取得し、その結果を連想配列、空配列、もしくは null で返す
     *
     * @return array<int, array{
     *   id: int,
     *   user_name: string,
     *   start_date: string,
     *   comment: string,
     *   correction_request_date: string,
     * }>|array<empty>|null
     */
    public function attendanceWaitingList(): array|null
    {
        $res = $this->attendanceRepository->findAttendanceWaitingList();

        if (!$res) {
            return null;
        }

        $user = $res['user'];
        $attendances = $res['attendances'];

        if ($attendances->count() === 0) {
            return [];
        }

        $resList = [];
        foreach ($attendances as $attendance) {
            $correction = $attendance->attendanceCorrections
                ->whereNull('approval_date')
                ->first();

            $resList[] = [
                'id' => $attendance->id,
                'user_name' => $user->name,
                'start_date' => $attendance->start_date,
                'comment' => $correction->comment,
                'correction_request_date' => $attendance->correction_request_date,
            ];
        }

        return $resList;
    }

    /**
     * 承認済み申請一覧を取得し、その結果を連想配列、空配列、もしくは null で返す
     *
     * @return array<int, array{
     *   id: int,
     *   user_name: string,
     *   start_date: string,
     *   comment: string,
     *   correction_request_date: string,
     * }>|array<empty>|null
     */
    public function attendanceApprovedList(): array|null
    {
        $res = $this->attendanceRepository->findAttendanceApprovedList();

        if (!$res) {
            return null;
        }

        $user = $res['user'];
        $attendanceCorrections = $res['attendance_corrections'];

        if ($attendanceCorrections->count() === 0) {
            return [];
        }

        $resList = [];
        foreach ($attendanceCorrections as $attendanceCorrection) {
            $resList[] = [
                'id' => $attendanceCorrection->id,
                'user_name' => $user->name,
                'start_date' => $attendanceCorrection->start_date,
                'comment' => $attendanceCorrection->comment,
                'correction_request_date' => $attendanceCorrection->correction_request_date,
            ];
        }

        return $resList;
    }
}