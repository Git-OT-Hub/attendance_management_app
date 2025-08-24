<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Attendance;
use App\Models\Breaking;
use App\Http\Requests\Attendance\WorkRequest;
use App\Http\Requests\Attendance\BreakingRequest;
use App\Http\Requests\Attendance\FinishBreakingRequest;
use App\Http\Requests\Attendance\FinishWorkRequest;

interface AttendanceRepositoryInterface
{
    /**
     * 勤務状態を確認し、その結果をAttendanceインスタンス、もしくは null で返す
     *
     * @param string $startTime
     * @return \App\Models\Attendance|null
     */
    public function checkWorkingState(string $startTime): Attendance|null;

    /**
     * 出勤処理を行い、その結果をAttendanceインスタンス、もしくは null で返す
     *
     * @param \App\Http\Requests\Attendance\WorkRequest $request
     * @return \App\Models\Attendance|null
     */
    public function createAttendanceDataTime(WorkRequest $request): Attendance|null;

    /**
     * 休憩開始処理を行い、その結果を連想配列、もしくは null で返す
     *
     * @param \App\Http\Requests\Attendance\BreakingRequest $request
     * @return array{attendance: \App\Models\Attendance, breaking: \App\Models\Breaking}|null
     */
    public function createStartBreak(BreakingRequest $request): array|null;

    /**
     * 休憩終了処理を行い、その結果をAttendanceインスタンス、もしくは null で返す
     *
     * @param \App\Http\Requests\Attendance\FinishBreakingRequest $request
     * @return \App\Models\Attendance|null
     */
    public function updateBreakEnd(FinishBreakingRequest $request): Attendance|null;

    /**
     * 退勤処理を行い、その結果をAttendanceインスタンス、もしくは null で返す
     *
     * @param \App\Http\Requests\Attendance\FinishWorkRequest $request
     * @return \App\Models\Attendance|null
     */
    public function updateClockOut(FinishWorkRequest $request): Attendance|null;

    /**
     * ログインユーザーの対象月の勤怠を取得
     *
     * @param string $date
     * @return \Illuminate\Database\Eloquent\Collection<string, \App\Models\Attendance>|null
     */
    public function findAttendanceList(string $date): Collection|null;

    /**
     * ログインユーザーの勤怠における詳細情報を取得し、その結果を連想配列、もしくは null で返す
     *
     * @param string $id
     * @return array{
     *   user: \App\Models\User,
     *   attendance: \App\Models\Attendance,
     *   breakings: \Illuminate\Database\Eloquent\Collection<int, \App\Models\Breaking>
     * }|null
     */
    public function findAttendanceShow(string $id): array|null;
}