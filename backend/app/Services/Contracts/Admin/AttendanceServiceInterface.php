<?php

namespace App\Services\Contracts\Admin;

use Illuminate\Http\Request;
use App\Http\Requests\Admin\Attendance\AttendanceCreateRequest;
use App\Http\Requests\Admin\Attendance\AttendanceCorrectionRequest;

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
    public function attendanceShow(string $id): array|null;

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
    public function correctAttendance(AttendanceCorrectionRequest $request): array|null;

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
    public function approveAttendance(Request $request): array|null;

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
    public function attendanceWaitingList(): array|null;

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
    public function attendanceApprovedList(): array|null;

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
    public function attendanceCorrectionShow(string $id): array|null;

    /**
     * スタッフ一覧を取得し、その結果を連想配列、空配列、もしくは null で返す
     *
     * @return array<int, array{
     *   id: int,
     *   name: string,
     *   email: string,
     * }>|array<empty>|null
     */
    public function getStaffList(): array|null;
}