<?php

namespace App\Repositories\Contracts;

use App\Models\Attendance;
use App\Http\Requests\Attendance\WorkRequest;

interface AttendanceRepositoryInterface
{
    /**
     * 出勤処理を行い、その結果をAttendanceインスタンスで返す
     *
     * @param \App\Http\Requests\Attendance\WorkRequest $request
     * @return \App\Models\Attendance|null
     */
    public function createAttendanceDataTime(WorkRequest $request): Attendance|null;
}