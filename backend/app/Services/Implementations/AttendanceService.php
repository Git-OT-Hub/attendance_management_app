<?php

namespace App\Services\Implementations;

use App\Services\Contracts\AttendanceServiceInterface;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Models\Attendance;
use App\Http\Requests\Attendance\WorkRequest;

class AttendanceService implements AttendanceServiceInterface
{
    private AttendanceRepositoryInterface $attendanceRepository;

    public function __construct(AttendanceRepositoryInterface $attendanceRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
    }

    /**
     * 出勤処理を行い、その結果をAttendanceインスタンスで返す
     *
     * @param \App\Http\Requests\Attendance\WorkRequest $request
     * @return \App\Models\Attendance|null
     */
    public function startWorking(WorkRequest $request): Attendance|null
    {
        $attendance = $this->attendanceRepository->createAttendanceDataTime($request);

        // 勤怠状態の日本語化
        if ($attendance) {
            $attendance->state = $attendance->convertAttendanceState();
        }

        return $attendance;
    }
}