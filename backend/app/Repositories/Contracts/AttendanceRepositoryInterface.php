<?php

namespace App\Repositories\Contracts;

use App\Models\Attendance;
use App\Http\Requests\Attendance\WorkRequest;

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
}