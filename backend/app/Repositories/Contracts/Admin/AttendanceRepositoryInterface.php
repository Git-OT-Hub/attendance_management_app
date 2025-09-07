<?php

namespace App\Repositories\Contracts\Admin;

use Illuminate\Database\Eloquent\Collection;
use App\Models\User;

interface AttendanceRepositoryInterface
{
    /**
     * 対象日における各一般ユーザーごとの勤怠情報を取得
     *
     * @param string $date
     * @return Collection<int, User>|null
     */
    public function findAttendanceTodayList(string $date): Collection|null;
}