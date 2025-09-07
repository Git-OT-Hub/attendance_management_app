<?php

namespace App\Repositories\Implementations\Admin;

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
                    'state' => $finishedState->value,
                ]);

                // 休憩データ作成
                Breaking::create([
                    'attendance_id' => $attendance->id,
                    'start_time' => Carbon::parse($request->breaking['breaking_start_time'])->format('Y-m-d H:i:s'),
                    'end_time' => Carbon::parse($request->breaking['breaking_end_time'])->format('Y-m-d H:i:s'),
                ]);

                // 勤怠データの一部カラム更新
                $totalBreakingTime = $attendance->totalBreakingTime();
                $actualWorkingTime = $attendance->actualWorkingTime(workEndTime: $attendance->end_time, totalBreakingTime: $totalBreakingTime);
                $attendance->update([
                    'total_breaking_time' => $totalBreakingTime,
                    'actual_working_time' => $actualWorkingTime,
                ]);

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
}