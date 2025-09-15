<?php

namespace App\Services\Implementations\Admin;

use Illuminate\Http\Request;
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

    /**
     * 勤怠修正申請の承認処理を行い、その結果を連想配列、もしくは null で返す
     *
     * @param Request $request
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
    public function approveAttendance(Request $request): array|null
    {
        $res = $this->attendanceRepository->updateApproveAttendance($request);

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
        $attendances = $this->attendanceRepository->findAttendanceWaitingList();

        if (!$attendances) {
            return null;
        }

        if ($attendances->count() === 0) {
            return [];
        }

        $resList = [];
        foreach ($attendances as $attendance) {
            $user = $attendance->user;
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
        $attendanceCorrections = $this->attendanceRepository->findAttendanceApprovedList();

        if (!$attendanceCorrections) {
            return null;
        }

        if ($attendanceCorrections->count() === 0) {
            return [];
        }

        $resList = [];
        foreach ($attendanceCorrections as $attendanceCorrection) {
            $user = $attendanceCorrection->attendance->user;

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

    /**
     * 一般ユーザーの勤怠修正履歴における詳細情報を取得し、その結果を連想配列、もしくは null で返す
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
     * スタッフ一覧を取得し、その結果を連想配列、空配列、もしくは null で返す
     *
     * @return array<int, array{
     *   id: int,
     *   name: string,
     *   email: string,
     * }>|array<empty>|null
     */
    public function getStaffList(): array|null
    {
        $users = $this->attendanceRepository->findUsers();

        if (!$users) {
            return null;
        }

        if ($users->count() === 0) {
            return [];
        }

        $resList = [];
        foreach ($users as $user) {
            $resList[] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ];
        }

        return $resList;
    }

    /**
     * スタッフ別で対象月の日付リストを生成し、その結果を連想配列、もしくは null で返す
     *
     * @param Request $request
     * @return array<int, array{
     *   id: int|null,
     *   date: string,
     *   start_time: string|null,
     *   end_time: string|null,
     *   total_breaking_time: string|null,
     *   actual_working_time: string|null,
     *   year_month: string
     * }>|null
     */
    public function attendanceMonthlyList(Request $request): array|null
    {
        $attendances = $this->attendanceRepository->findAttendanceMonthlyList($request);
        $date = (string)$request->query('month') . '-01';

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
                    $correction = $attendance->attendanceCorrections()
                        ->whereNull('approval_date')
                        ->orderByDesc('id')
                        ->first();

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
     * スタッフ別で対象月の勤怠一覧情報のCSVを生成し、その結果を連想配列、もしくは null で返す
     *
     * @param Request $request
     * @return array{
     *   downloadCsvCallback: callable,
     *   fileName: string,
     *   responseHeader: array<string,string>,
     * }|null
     */
    public function attendanceMonthlyListDownload(Request $request): array|null
    {
        try {
            // 対象ユーザー取得
            $user = $this->attendanceRepository->findUser($request);
            if (!$user) {
                return null;
            }
            // 対象年月取得
            $date = (string)$request->query('month') . '-01';
            $formatDate = Carbon::parse($date)->format('Y年m月');
            // 対象年月の勤怠情報取得
            $attendances = $this->attendanceMonthlyListForCsv($request);
            if (!$attendances) {
                return null;
            }

            // CSVファイル作成コールバック
            $downloadCsvCallback = function () use ($user, $formatDate, $attendances) {
                // CSVファイル作成
                $csv = fopen('php://output', 'w');

                // Mac / Windows 両方での文字化け対策
                fwrite($csv, "\xEF\xBB\xBF");

                // CSVの1行目
                $userName = [$user->name];
                fputcsv($csv, $userName);

                // CSVの2行目
                $targetYearMonth = [$formatDate];
                fputcsv($csv, $targetYearMonth);

                // CSVの3行目
                $column = [
                    'date' => '日付',
                    'start_time' => '出勤',
                    'end_time' => '退勤',
                    'total_breaking_time' => '休憩',
                    'actual_working_time' => '合計',
                ];
                fputcsv($csv, $column);

                // CSVの4行目以降
                foreach ($attendances as $attendance) {
                    $attendanceData = [
                        'date' => $attendance['date'],
                        'start_time' => $attendance['start_time'],
                        'end_time' => $attendance['end_time'],
                        'total_breaking_time' => $attendance['total_breaking_time'],
                        'actual_working_time' => $attendance['actual_working_time'],
                    ];
                    fputcsv($csv, $attendanceData);
                }

                // CSVファイルを閉じる
                fclose($csv);
            };

            // ファイル名
            $fileName = (string)$user->name . '_' . (string)$formatDate . '.csv';

            // レスポンスヘッダー情報
            $responseHeader = [
                'Content-Type' => 'text/csv',
                'Access-Control-Expose-Headers' => 'Content-Disposition'
            ];

            return [
                'downloadCsvCallback' => $downloadCsvCallback,
                'fileName' => $fileName,
                'responseHeader' => $responseHeader
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * スタッフ別で対象月の日付リストを生成し、その結果を連想配列、もしくは null で返す
     *
     * @param Request $request
     * @return array<int, array{
     *   date: string,
     *   start_time: string|null,
     *   end_time: string|null,
     *   total_breaking_time: string|null,
     *   actual_working_time: string|null,
     * }>|null
     */
    private function attendanceMonthlyListForCsv(Request $request): array|null
    {
        try {
            $attendances = $this->attendanceRepository->findAttendanceMonthlyList($request);

            if (!$attendances) {
                return null;
            }

            // 日付ごとに配列を作る
            $res = [];
            $date = (string)$request->query('month') . '-01';
            $startOfMonth = Carbon::parse($date)->startOfMonth();
            $endOfMonth = Carbon::parse($date)->endOfMonth();

            for ($dateTime = $startOfMonth; $dateTime->lte($endOfMonth); $dateTime->addDay()) {
                $dateStr = $dateTime->toDateString();
                $attendance = $attendances->get($dateStr);

                $res[] = [
                    'date' => $dateTime->format('m/d') . '(' . $this->getDayOfWeek($dateTime) . ')',
                    'start_time' => $attendance?->start_time ? Carbon::parse($attendance->start_time)->format('H:i') : null,
                    'end_time' => $attendance?->end_time ? Carbon::parse($attendance->end_time)->format('H:i') : null,
                    'total_breaking_time' => $this->formatSecondsToHoursMinutes($attendance?->total_breaking_time),
                    'actual_working_time' => $this->formatSecondsToHoursMinutes($attendance?->actual_working_time),
                ];
            }

            return $res;
        } catch (\Throwable $e) {
            return null;
        }
    }
}