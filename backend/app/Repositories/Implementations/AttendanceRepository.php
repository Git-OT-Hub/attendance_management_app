<?php

namespace App\Repositories\Implementations;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Models\Attendance;
use App\Models\Breaking;
use App\Http\Requests\Attendance\WorkRequest;
use App\Http\Requests\Attendance\BreakingRequest;

class AttendanceRepository implements AttendanceRepositoryInterface
{
    /**
     * 勤務状態を確認し、その結果をAttendanceインスタンス、もしくは null で返す
     *
     * @param string $startTime
     * @return \App\Models\Attendance|null
     */
    public function checkWorkingState(string $startTime): Attendance|null
    {
        $userId = Auth::id();
        $dateOnly = Carbon::parse($startTime)->toDateString();

        // 渡された日付に合致するデータを取得
        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('start_date', '=', date('Y-m-d', strtotime($dateOnly)))
            ->first();

        if (!$attendance) {
            return null;
        }

        return $attendance;
    }

    /**
     * 出勤処理を行い、その結果をAttendanceインスタンス、もしくは null で返す
     *
     * @param \App\Http\Requests\Attendance\WorkRequest $request
     * @return \App\Models\Attendance|null
     */
    public function createAttendanceDataTime(WorkRequest $request): Attendance|null
    {
        $userId = Auth::id();
        $startTime = $request->start_time;
        $dateOnly = Carbon::parse($startTime)->toDateString();

        // 同じ日付で既に登録されていないか検証
        $exists = Attendance::where('user_id', $userId)
            ->whereDate('start_date', '=', date('Y-m-d', strtotime($dateOnly)))
            ->exists();

        if ($exists) {
            return null;
        }

        $attendance = Attendance::create([
            'user_id' => $userId,
            'start_date' => $dateOnly,
            'start_time' => $startTime,
            'state' => (int)$request->state,
        ]);

        return $attendance;
    }

    /**
     * 休憩開始処理を行い、その結果を連想配列、もしくは null で返す
     *
     * @param \App\Http\Requests\Attendance\BreakingRequest $request
     * @return array{attendance: \App\Models\Attendance, breaking: \App\Models\Breaking}|null
     */
    public function createStartBreak(BreakingRequest $request): array|null
    {
        try {
            $res = DB::transaction(function () use($request) {
                $attendance = Attendance::find($request->attendance_id);

                // 勤怠状態を更新
                $attendance->update([
                    'state' => (int)$request->state,
                ]);

                // 休憩データ新規作成
                $breaking = Breaking::create([
                    'attendance_id' => $request->attendance_id,
                    'start_time' => $request->start_time,
                ]);

                return [
                    'attendance' => $attendance->fresh(),
                    'breaking'   => $breaking,
                ];
            });

            return $res;
        } catch (\Throwable $e) {
            return null;
        }
    }
}