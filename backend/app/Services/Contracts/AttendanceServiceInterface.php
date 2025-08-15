<?php

namespace App\Services\Contracts;

use App\Models\Attendance;
use App\Http\Requests\Attendance\WorkRequest;

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
}