<?php

namespace App\Repositories\Contracts\Admin;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Breaking;
use App\Models\AttendanceCorrection;
use App\Models\BreakingCorrection;
use App\Http\Requests\Admin\Attendance\AttendanceCreateRequest;
use App\Http\Requests\Admin\Attendance\AttendanceCorrectionRequest;

interface AttendanceRepositoryInterface
{
    /**
     * 対象日における各一般ユーザーごとの勤怠情報を取得
     *
     * @param string $date
     * @return Collection<int, User>|null
     */
    public function findAttendanceTodayList(string $date): Collection|null;

    /**
     * 勤怠新規登録を行い、その結果を Attendanceインスタンス もしくは null で返す
     *
     * @param AttendanceCreateRequest $request
     * @return Attendance|null
     */
    public function createAttendanceRecords(AttendanceCreateRequest $request): Attendance|null;

    /**
     * 一般ユーザーの勤怠における詳細情報を取得し、その結果を連想配列、もしくは null で返す
     *
     * @param string $id
     * @return array{
     *   user: User,
     *   attendance: Attendance|AttendanceCorrection,
     *   breakings: Collection<int, Breaking|BreakingCorrection>
     * }|null
     */
    public function findAttendanceShow(string $id): array|null;

    /**
     * 勤怠修正処理を行い、その結果を連想配列、もしくは null で返す
     *
     * @param AttendanceCorrectionRequest $request
     * @return array{
     *   user: User,
     *   attendance: Attendance,
     *   breakings: Collection<int, Breaking>
     * }|null
     */
    public function updateAttendanceCorrection(AttendanceCorrectionRequest $request): array|null;

    /**
     * 勤怠修正申請の承認処理を行い、その結果を連想配列、もしくは null で返す
     *
     * @param Request $request
     * @return array{
     *   user: User,
     *   attendance: Attendance,
     *   breakings: Collection<int, Breaking>
     * }|null
     */
    public function updateApproveAttendance(Request $request): array|null;

    /**
     * 修正依頼申請中の勤怠情報一覧を取得
     *
     * @return Collection<int, Attendance>|null
     */
    public function findAttendanceWaitingList(): Collection|null;

    /**
     * 勤怠修正履歴の情報一覧を取得
     *
     * @return Collection<int, AttendanceCorrection>|null
     */
    public function findAttendanceApprovedList(): Collection|null;

    /**
     * 一般ユーザーの勤怠修正履歴における詳細情報を取得し、その結果を連想配列、もしくは null で返す
     *
     * @param string $id
     * @return array{
     *   user: User,
     *   attendance_correction: AttendanceCorrection,
     *   breaking_corrections: Collection<int, BreakingCorrection>
     * }|null
     */
    public function findAttendanceCorrectionShow(string $id): array|null;

    /**
     * 全ユーザー情報を取得
     *
     * @return Collection<int, User>|null
     */
    public function findUsers(): Collection|null;

    /**
     * 対象ユーザーの対象月の勤怠を取得
     *
     * @param Request $request
     * @return Collection<string, Attendance>|null
     */
    public function findAttendanceMonthlyList(Request $request): Collection|null;

    /**
     * 対象ユーザー情報を取得
     *
     * @param Request $request
     * @return User|null
     */
    public function findUser(Request $request): User|null;
}