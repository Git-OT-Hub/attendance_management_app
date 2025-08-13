<?php

namespace App\Repositories\Implementations;

use Illuminate\Support\Facades\Auth;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Models\Attendance;
use App\Http\Requests\Attendance\WorkRequest;

class AttendanceRepository implements AttendanceRepositoryInterface
{
    /**
     * 出勤処理を行い、その結果をAttendanceインスタンスで返す
     *
     * @param \App\Http\Requests\Attendance\WorkRequest $request
     * @return \App\Models\Attendance|null
     */
    public function createAttendanceDataTime(WorkRequest $request): Attendance|null
    {
        $userId = Auth::id();
        $startTime = $request->start_time;

        // 同じ日付で既に登録されていないか検証
        $exists = Attendance::where('user_id', $userId)
            ->whereDate('start_time', '=', date('Y-m-d', strtotime($startTime)))
            ->exists();

        if ($exists) {
            return null;
        }

        $attendance = Attendance::create([
            'user_id' => $userId,
            'start_time' => $startTime,
            'state' => (int)$request->state,
        ]);

        return $attendance;
    }
}