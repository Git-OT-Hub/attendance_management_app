<?php

namespace App\Services\Contracts\Admin;

use App\Http\Requests\Admin\Attendance\AttendanceCreateRequest;

interface AttendanceServiceInterface
{
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
    public function attendanceTodayList(string $date): array|null;

    /**
     * 勤怠新規登録を行い、その結果を 整数 もしくは null で返す
     *
     * @param AttendanceCreateRequest $request
     * @return int|null
     */
    public function createAttendance(AttendanceCreateRequest $request): int|null;
}