<?php

namespace App\Services\Contracts;

use App\Models\Attendance;
use App\Http\Requests\Attendance\WorkRequest;
use App\Http\Requests\Attendance\BreakingRequest;
use App\Http\Requests\Attendance\FinishBreakingRequest;
use App\Http\Requests\Attendance\FinishWorkRequest;

interface AttendanceServiceInterface
{
    /**
     * 勤務状態を確認し、その結果をAttendanceインスタンス、もしくは null で返す
     *
     * @param string $startTime
     * @return \App\Models\Attendance|null
     */
    public function workingState(string $startTime): Attendance|null;

    /**
     * 出勤処理を行い、その結果をAttendanceインスタンス、もしくは null で返す
     *
     * @param \App\Http\Requests\Attendance\WorkRequest $request
     * @return \App\Models\Attendance|null
     */
    public function startWorking(WorkRequest $request): Attendance|null;

    /**
     * 休憩開始処理を行い、その結果を連想配列、もしくは null で返す
     *
     * @param \App\Http\Requests\Attendance\BreakingRequest $request
     * @return array{breaking_id: int, state: string}|null
     */
    public function startBreak(BreakingRequest $request): array|null;

    /**
     * 休憩終了処理を行い、その結果を連想配列、もしくは null で返す
     *
     * @param \App\Http\Requests\Attendance\FinishBreakingRequest $request
     * @return array{state: string}|null
     */
    public function breakEnd(FinishBreakingRequest $request): array|null;

    /**
     * 退勤処理を行い、その結果を連想配列、もしくは null で返す
     *
     * @param \App\Http\Requests\Attendance\FinishWorkRequest $request
     * @return array{state: string}|null
     */
    public function clockOut(FinishWorkRequest $request): array|null;
}