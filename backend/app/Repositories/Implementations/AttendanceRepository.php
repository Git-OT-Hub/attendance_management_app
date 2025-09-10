<?php

namespace App\Repositories\Implementations;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Breaking;
use App\Models\AttendanceCorrection;
use App\Models\BreakingCorrection;
use App\Http\Requests\Attendance\WorkRequest;
use App\Http\Requests\Attendance\BreakingRequest;
use App\Http\Requests\Attendance\FinishBreakingRequest;
use App\Http\Requests\Attendance\FinishWorkRequest;
use App\Http\Requests\Attendance\AttendanceCorrectionRequest;
use App\Http\Requests\Attendance\AttendanceCreateRequest;
use App\Enums\AttendanceState;

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
                $finishedState = AttendanceState::FINISHED;

                if ($attendance->state === $finishedState->value) {
                    return null;
                }

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

    /**
     * 休憩終了処理を行い、その結果をAttendanceインスタンス、もしくは null で返す
     *
     * @param \App\Http\Requests\Attendance\FinishBreakingRequest $request
     * @return \App\Models\Attendance|null
     */
    public function updateBreakEnd(FinishBreakingRequest $request): Attendance|null
    {
        try {
            $res = DB::transaction(function () use($request) {
                $attendance = Attendance::find($request->attendance_id);
                $finishedState = AttendanceState::FINISHED;

                if ($attendance->state === $finishedState->value) {
                    return null;
                }

                // 勤怠状態を更新
                $attendance->update([
                    'state' => (int)$request->state,
                ]);

                // 休憩データの更新
                $breaking = Breaking::find($request->breaking_id);
                $breaking->update([
                    'end_time' => $request->end_time,
                ]);

                return $attendance->fresh();
            });

            return $res;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * 退勤処理を行い、その結果をAttendanceインスタンス、もしくは null で返す
     *
     * @param \App\Http\Requests\Attendance\FinishWorkRequest $request
     * @return \App\Models\Attendance|null
     */
    public function updateClockOut(FinishWorkRequest $request): Attendance|null
    {
        try {
            $workEndTime = $request->end_time;
            $attendance = Attendance::find($request->attendance_id);
            $finishedState = AttendanceState::FINISHED;

            if ($attendance->state === $finishedState->value) {
                return null;
            }

            // 休憩時間の合計を取得
            $totalBreakingTime = $attendance->totalBreakingTime();
            // 実勤務時間を取得
            $actualWorkingTime = $attendance->actualWorkingTime(workEndTime: $workEndTime, totalBreakingTime: $totalBreakingTime);

            $attendance->update([
                'end_time' => $workEndTime,
                'total_breaking_time' => $totalBreakingTime,
                'actual_working_time' => $actualWorkingTime,
                'state' => (int)$request->state,
            ]);

            return $attendance->fresh();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * ログインユーザーの対象月の勤怠を取得
     *
     * @param string $date
     * @return \Illuminate\Database\Eloquent\Collection<string, \App\Models\Attendance>|null
     */
    public function findAttendanceList(string $date): Collection|null
    {
        try {
            $userId = Auth::id();
            $startOfMonth = Carbon::parse($date)->startOfMonth();
            $endOfMonth = Carbon::parse($date)->endOfMonth();

            // ログインユーザーの対象月の勤怠を取得
            $attendances = Attendance::with(['attendanceCorrections' => function ($query) {
                $query->whereNull('approval_date');
            }])
                ->where('user_id', $userId)
                ->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                ->get()
                ->keyBy('start_date');

            return $attendances;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * ログインユーザーの勤怠における詳細情報を取得し、その結果を連想配列、もしくは null で返す
     *
     * @param string $id
     * @return array{
     *   user: \App\Models\User,
     *   attendance: \App\Models\Attendance|\App\Models\AttendanceCorrection,
     *   breakings: \Illuminate\Database\Eloquent\Collection<int, \App\Models\Breaking|\App\Models\BreakingCorrection>
     * }|null
     */
    public function findAttendanceShow(string $id): array|null
    {
        try {
            $attendance = Attendance::find($id);
            $user = Auth::user();

            if ($attendance->correction_request_date) {
                $attendanceCorrections = $attendance->attendanceCorrections()->where('approval_date', null)->get();
                if (count($attendanceCorrections) !== 1) {
                    return null;
                }
                $attendanceCorrection = $attendanceCorrections->first();

                return [
                    'user'       => $user,
                    'attendance' => $attendanceCorrection,
                    'breakings'  => $attendanceCorrection->breakingCorrections()->orderBy('id', 'asc')->get(),
                ];
            } else {
                return [
                    'user'       => $user,
                    'attendance' => $attendance,
                    'breakings'  => $attendance->breakings()->orderBy('id', 'asc')->get(),
                ];
            }
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * ログインユーザーの勤怠情報の修正を行い、その結果を連想配列、もしくは null で返す
     *
     * @param AttendanceCorrectionRequest $request
     * @return array{
     *   user: \App\Models\User,
     *   attendance: \App\Models\AttendanceCorrection,
     *   breakings: \Illuminate\Database\Eloquent\Collection<int, \App\Models\BreakingCorrection>
     * }|null
     */
    public function updateAttendanceCorrection(AttendanceCorrectionRequest $request): array|null
    {
        try {
            $res = DB::transaction(function () use($request) {
                $attendanceId = $request->attendance['attendance_id'];
                $user = Auth::user();

                // 登録されている勤怠情報の一部データ更新
                $attendance = Attendance::find($attendanceId);
                if ($attendance->user_id !== $user->id) {
                    return null;
                }
                $attendance->update([
                    'correction_request_date' => Carbon::parse($request->correction_request_date)->format('Y-m-d H:i:s'),
                ]);
                $attendance = $attendance->fresh();

                // 勤怠修正履歴の作成
                $attendanceCorrection = AttendanceCorrection::create([
                    'attendance_id' => $attendance->id,
                    'start_date' => Carbon::parse($attendance->start_date)->format('Y-m-d'),
                    'start_time' => Carbon::parse($request->attendance['attendance_start_time'])->format('Y-m-d H:i:s'),
                    'end_time' => Carbon::parse($request->attendance['attendance_end_time'])->format('Y-m-d H:i:s'),
                    'total_breaking_time' => 0,
                    'actual_working_time' => 0,
                    'comment' => $request->comment,
                    'correction_request_date' => Carbon::parse($request->correction_request_date)->format('Y-m-d H:i:s'),
                    'state' => $attendance->state,
                ]);

                // 休憩修正履歴の作成
                $sortedBreakings = collect($request->breakings)
                    // 空配列を除外
                    ->filter(function ($breaking) {
                        return !empty($breaking['breaking_start_time']) && !empty($breaking['breaking_end_time']);
                    })
                    // breaking_start_time の昇順で並べ替え
                    ->sortBy(function ($breaking) {
                        return Carbon::parse($breaking['breaking_start_time']);
                    });
                foreach ($sortedBreakings as $label => $breaking) {
                    if (!empty($breaking['breaking_start_time']) && !empty($breaking['breaking_end_time'])) {
                        BreakingCorrection::create([
                            'attendance_correction_id' => $attendanceCorrection->id,
                            'start_time' => Carbon::parse($breaking['breaking_start_time'])->format('Y-m-d H:i:s'),
                            'end_time' => Carbon::parse($breaking['breaking_end_time'])->format('Y-m-d H:i:s'),
                        ]);
                    }
                }

                // 勤怠修正履歴に休憩時間、実勤務時間を登録
                $totalBreakingTime = $attendanceCorrection->totalBreakingTime();
                $actualWorkingTime = $attendanceCorrection->actualWorkingTime($totalBreakingTime);
                $attendanceCorrection->update([
                    'total_breaking_time' => $totalBreakingTime,
                    'actual_working_time' => $actualWorkingTime,
                ]);

                return [
                    'user'       => $user,
                    'attendance' => $attendanceCorrection->fresh(),
                    'breakings'  => $attendanceCorrection->breakingCorrections()->orderBy('id', 'asc')->get(),
                ];
            });

            return $res;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * 勤怠新規登録を行い、その結果を Attendanceインスタンス もしくは null で返す
     *
     * @param AttendanceCreateRequest $request
     * @return Attendance|null
     */
    public function createAttendanceRecords(AttendanceCreateRequest $request): Attendance|null
    {
        try {
            $res = DB::transaction(function () use($request) {
                $userId = Auth::id();
                $dateOnly = Carbon::parse($request->attendance['attendance_start_date'])->toDateString();
                $finishedState = AttendanceState::FINISHED;

                // 同じ日付で既に登録されていないか検証
                $exists = Attendance::where('user_id', $userId)
                    ->whereDate('start_date', '=', $dateOnly)
                    ->exists();
                if ($exists) {
                    return null;
                }

                // 勤怠データ作成
                $attendance = Attendance::create([
                    'user_id' => $userId,
                    'start_date' => Carbon::parse($request->attendance['attendance_start_date'])->format('Y-m-d'),
                    'start_time' => Carbon::parse($request->attendance['attendance_start_time'])->format('Y-m-d H:i:s'),
                    'end_time' => Carbon::parse($request->attendance['attendance_end_time'])->format('Y-m-d H:i:s'),
                    'correction_request_date' => Carbon::parse($request->correction_request_date)->format('Y-m-d H:i:s'),
                    'state' => $finishedState->value,
                ]);

                // 休憩データ作成
                if (!empty($request->breaking['breaking_start_time']) && !empty($request->breaking['breaking_end_time'])) {
                    Breaking::create([
                        'attendance_id' => $attendance->id,
                        'start_time' => Carbon::parse($request->breaking['breaking_start_time'])->format('Y-m-d H:i:s'),
                        'end_time' => Carbon::parse($request->breaking['breaking_end_time'])->format('Y-m-d H:i:s'),
                    ]);
                }

                // 勤怠データの一部カラム更新
                $totalBreakingTime = $attendance->totalBreakingTime();
                $actualWorkingTime = $attendance->actualWorkingTime(workEndTime: $attendance->end_time, totalBreakingTime: $totalBreakingTime);
                $attendance->update([
                    'total_breaking_time' => $totalBreakingTime,
                    'actual_working_time' => $actualWorkingTime,
                ]);

                // 勤怠修正履歴データ作成
                $attendanceCorrection = AttendanceCorrection::create([
                    'attendance_id' => $attendance->id,
                    'start_date' => Carbon::parse($request->attendance['attendance_start_date'])->format('Y-m-d'),
                    'start_time' => Carbon::parse($request->attendance['attendance_start_time'])->format('Y-m-d H:i:s'),
                    'end_time' => Carbon::parse($request->attendance['attendance_end_time'])->format('Y-m-d H:i:s'),
                    'total_breaking_time' => $totalBreakingTime,
                    'actual_working_time' => $actualWorkingTime,
                    'comment' => $request->comment,
                    'correction_request_date' => Carbon::parse($request->correction_request_date)->format('Y-m-d H:i:s'),
                    'state' => $finishedState->value,
                ]);

                // 休憩修正履歴データの作成
                if (!empty($request->breaking['breaking_start_time']) && !empty($request->breaking['breaking_end_time'])) {
                    BreakingCorrection::create([
                        'attendance_correction_id' => $attendanceCorrection->id,
                        'start_time' => Carbon::parse($request->breaking['breaking_start_time'])->format('Y-m-d H:i:s'),
                        'end_time' => Carbon::parse($request->breaking['breaking_end_time'])->format('Y-m-d H:i:s'),
                    ]);
                }

                return $attendance;
            });

            return $res;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * ログインユーザー情報、勤怠情報を取得
     *
     * @return array{
     *   user: User,
     *   attendances: Collection<int, Attendance>
     * }|null
     */
    public function findAttendanceWaitingList(): array|null
    {
        try {
            $user = Auth::user();
            $attendances = Attendance::with(['attendanceCorrections' => function ($query) {
                $query->whereNull('approval_date');
            }])
                ->where('user_id', $user->id)
                ->whereNotNull('correction_request_date')
                ->orderBy('correction_request_date', 'desc')
                ->get();

            return [
                'user' => $user,
                'attendances' => $attendances
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }
}