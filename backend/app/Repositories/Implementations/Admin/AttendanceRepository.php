<?php

namespace App\Repositories\Implementations\Admin;

use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;
use App\Repositories\Contracts\Admin\AttendanceRepositoryInterface;
use App\Models\User;

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
}