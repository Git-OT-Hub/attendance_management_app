<?php

namespace App\Repositories\Implementations\Admin;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Repositories\Contracts\Admin\AttendanceRepositoryInterface;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Breaking;
use App\Models\AttendanceCorrection;
use App\Models\BreakingCorrection;
use App\Http\Requests\Admin\Attendance\AttendanceCreateRequest;
use App\Http\Requests\Admin\Attendance\AttendanceCorrectionRequest;
use App\Enums\AttendanceState;

class AttendanceRepository implements AttendanceRepositoryInterface
{
    /**
     * 対象日における各一般ユーザーごとの勤怠情報を取得
     *
     * @param string $date
     * @return Collection<int, User>|null
     */
    public function findAttendanceTodayList(string $date): Collection|null
    {
        try {
            $dateOnly = Carbon::parse($date)->toDateString();

            $users = User::with([
                'attendances' => function ($query) use ($dateOnly) {
                    $query->whereDate('start_date', '=', $dateOnly);
                },
                'attendances.attendanceCorrections' => function ($query) {
                    $query->whereNull('approval_date');
                }
            ])
                ->get();

            return $users;
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
                $userId = $request->attendance['user_id'];
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
                    'correction_request_date' => null,
                    'is_approved_history' => true,
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
                    'approval_date' => Carbon::parse($request->correction_request_date)->format('Y-m-d H:i:s'),
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
     * 一般ユーザーの勤怠における詳細情報を取得し、その結果を連想配列、もしくは null で返す
     *
     * @param string $id
     * @return array{
     *   user: User,
     *   attendance: Attendance|AttendanceCorrection,
     *   breakings: Collection<int, Breaking|BreakingCorrection>
     * }|null
     */
    public function findAttendanceShow(string $id): array|null
    {
        try {
            $attendance = Attendance::find($id);
            $user = User::find($attendance->user_id);

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
     * 勤怠修正処理を行い、その結果を連想配列、もしくは null で返す
     *
     * @param AttendanceCorrectionRequest $request
     * @return array{
     *   user: User,
     *   attendance: Attendance,
     *   breakings: Collection<int, Breaking>
     * }|null
     */
    public function updateAttendanceCorrection(AttendanceCorrectionRequest $request): array|null
    {
        try {
            $res = DB::transaction(function () use($request) {
                $attendanceId = $request->attendance['attendance_id'];
                $attendance = Attendance::find($attendanceId);
                $user = User::find($attendance->user_id);
                $finishedState = AttendanceState::FINISHED;

                // 登録されている勤怠情報の更新
                $attendance->update([
                    'start_time' => Carbon::parse($request->attendance['attendance_start_time'])->format('Y-m-d H:i:s'),
                    'end_time' => Carbon::parse($request->attendance['attendance_end_time'])->format('Y-m-d H:i:s'),
                    'total_breaking_time' => 0,
                    'actual_working_time' => 0,
                    'correction_request_date' => null,
                    'is_approved_history' => true,
                    'state' => $finishedState->value,
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
                    'approval_date' => Carbon::parse($request->correction_request_date)->format('Y-m-d H:i:s'),
                    'state' => $attendance->state,
                ]);

                // 休憩データを全て削除
                $attendance->breakings()->delete();

                // 休憩データの再作成 と 休憩修正履歴の作成
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
                        Breaking::create([
                            'attendance_id' => $attendance->id,
                            'start_time' => Carbon::parse($breaking['breaking_start_time'])->format('Y-m-d H:i:s'),
                            'end_time' => Carbon::parse($breaking['breaking_end_time'])->format('Y-m-d H:i:s'),
                        ]);

                        BreakingCorrection::create([
                            'attendance_correction_id' => $attendanceCorrection->id,
                            'start_time' => Carbon::parse($breaking['breaking_start_time'])->format('Y-m-d H:i:s'),
                            'end_time' => Carbon::parse($breaking['breaking_end_time'])->format('Y-m-d H:i:s'),
                        ]);
                    }
                }

                // 勤怠と勤怠修正履歴に休憩時間、実勤務時間を登録
                $totalBreakingTime = $attendanceCorrection->totalBreakingTime();
                $actualWorkingTime = $attendanceCorrection->actualWorkingTime($totalBreakingTime);
                $attendanceCorrection->update([
                    'total_breaking_time' => $totalBreakingTime,
                    'actual_working_time' => $actualWorkingTime,
                ]);
                $attendance->update([
                    'total_breaking_time' => $totalBreakingTime,
                    'actual_working_time' => $actualWorkingTime,
                ]);

                return [
                    'user'       => $user,
                    'attendance' => $attendance->fresh(),
                    'breakings'  => $attendance->breakings()->orderBy('id', 'asc')->get(),
                ];
            });

            return $res;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * 勤怠修正申請の承認処理を行い、その結果を連想配列、もしくは null で返す
     *
     * @param Request $request
     * @return array{
     *   user: User,
     *   attendance: Attendance,
     *   breakings: Collection<int, Breaking>
     * }|null
     */
    public function updateApproveAttendance(Request $request): array|null
    {
        try {
            $res = DB::transaction(function () use($request) {
                $attendanceCorrectionId = $request->attendance_correction_id;
                $attendanceCorrection = AttendanceCorrection::find($attendanceCorrectionId);
                $breakingCorrections = $attendanceCorrection->breakingCorrections()->orderBy('id', 'asc')->get();
                $attendance = Attendance::find($attendanceCorrection->attendance_id);
                $user = User::find($attendance->user_id);
                $finishedState = AttendanceState::FINISHED;

                if ($attendanceCorrection->approval_date) {
                    return null;
                }

                // 勤怠テーブルの修正
                $attendance->update([
                    'start_time' => $attendanceCorrection->start_time,
                    'end_time' => $attendanceCorrection->end_time,
                    'total_breaking_time' => $attendanceCorrection->total_breaking_time,
                    'actual_working_time' => $attendanceCorrection->actual_working_time,
                    'correction_request_date' => null,
                    'is_approved_history' => true,
                    'state' => $finishedState->value,
                ]);

                // 休憩テーブルの該当データを全て削除
                $attendance->breakings()->delete();
                // 休憩テーブルに更新後の休憩データを追加
                foreach ($breakingCorrections as $breakingCorrection) {
                    Breaking::create([
                        'attendance_id' => $attendance->id,
                        'start_time' => $breakingCorrection->start_time,
                        'end_time' => $breakingCorrection->end_time,
                    ]);
                }

                // 勤怠修正テーブルの更新
                $attendanceCorrection->update([
                    'approval_date' => Carbon::parse($request->approval_date)->format('Y-m-d H:i:s'),
                ]);

                $attendance = $attendance->fresh();

                return [
                    'user'       => $user,
                    'attendance' => $attendance,
                    'breakings'  => $attendance->breakings()->orderBy('id', 'asc')->get(),
                ];
            });

            return $res;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * 修正依頼申請中の勤怠情報一覧を取得
     *
     * @return Collection<int, Attendance>|null
     */
    public function findAttendanceWaitingList(): Collection|null
    {
        try {
            $attendances = Attendance::with([
                'user',
                'attendanceCorrections' => function ($query) {
                    $query->whereNull('approval_date');
                }
            ])
                ->whereNotNull('correction_request_date')
                ->orderBy('correction_request_date', 'desc')
                ->get();

            return $attendances;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * 勤怠修正履歴の情報一覧を取得
     *
     * @return Collection<int, AttendanceCorrection>|null
     */
    public function findAttendanceApprovedList(): Collection|null
    {
        try {
            $attendanceCorrections = AttendanceCorrection::with([
                'attendance.user',
            ])
                ->whereNotNull('approval_date')
                ->orderBy('approval_date', 'desc')
                ->get();

            return $attendanceCorrections;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * 一般ユーザーの勤怠修正履歴における詳細情報を取得し、その結果を連想配列、もしくは null で返す
     *
     * @param string $id
     * @return array{
     *   user: User,
     *   attendance_correction: AttendanceCorrection,
     *   breaking_corrections: Collection<int, BreakingCorrection>
     * }|null
     */
    public function findAttendanceCorrectionShow(string $id): array|null
    {
        try {
            $attendanceCorrection = AttendanceCorrection::find($id);
            $user = $attendanceCorrection->attendance->user;

            return [
                'user'       => $user,
                'attendance_correction' => $attendanceCorrection,
                'breaking_corrections'  => $attendanceCorrection->breakingCorrections()->orderBy('id', 'asc')->get(),
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * 全ユーザー情報を取得
     *
     * @return Collection<int, User>|null
     */
    public function findUsers(): Collection|null
    {
        try {
            $users = User::all();

            return $users;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * 対象ユーザーの対象月の勤怠を取得
     *
     * @param Request $request
     * @return Collection<string, Attendance>|null
     */
    public function findAttendanceMonthlyList(Request $request): Collection|null
    {
        try {
            $date = (string)$request->query('month') . '-01';
            $userId = (int)$request->query('userId');
            $startOfMonth = Carbon::parse($date)->startOfMonth();
            $endOfMonth = Carbon::parse($date)->endOfMonth();

            // 対象ユーザーの対象月の勤怠を取得
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
     * 対象ユーザー情報を取得
     *
     * @param Request $request
     * @return User|null
     */
    public function findUser(Request $request): User|null
    {
        try {
            $userId = (int)$request->query('userId');
            $user = User::find($userId);

            return $user;
        } catch (\Throwable $e) {
            return null;
        }
    }
}