<?php

namespace App\Services\Contracts;

use App\Models\Attendance;
use App\Http\Requests\Attendance\WorkRequest;

interface AttendanceServiceInterface
{
    /**
     * 出勤処理を行い、その結果をAttendanceインスタンスで返す
     *
     * @param \App\Http\Requests\Attendance\WorkRequest $request
     * @return \App\Models\Attendance|null
     */
    public function startWorking(WorkRequest $request): Attendance|null;
}