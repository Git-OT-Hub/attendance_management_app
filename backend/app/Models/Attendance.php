<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use App\Enums\AttendanceState;

class Attendance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'start_date',
        'start_time',
        'end_time',
        'total_breaking_time',
        'actual_working_time',
        'correction_request_date',
        'is_approved_history',
        'state',
    ];

    /**
     * 勤怠情報に紐づくユーザーを取得するリレーション
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 勤怠に紐づく休憩情報を取得するリレーション
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Breaking>
     */
    public function breakings(): HasMany
    {
        return $this->hasMany(Breaking::class);
    }

    /**
     * 勤怠に紐づく勤怠修正情報を取得するリレーション
     *
     * @return HasMany<\App\Models\AttendanceCorrection>
     */
    public function attendanceCorrections(): HasMany
    {
        return $this->hasMany(AttendanceCorrection::class);
    }

    /**
     * 勤怠状態を日本語化する
     *
     * @return string
     */
    public function convertAttendanceState(): string
    {
        $attendanceState = "";
        $states = AttendanceState::cases();
        foreach ($states as $state) {
            if ((int)$state->value === $this->state) {
                $attendanceState = $state->label();
            }
        }

        return $attendanceState;
    }

    /**
     * 休憩時間の合計を算出する
     *
     * @return int
     */
    public function totalBreakingTime(): int
    {
        $res = 0;
        $breakings = $this->breakings;

        foreach ($breakings as $breaking) {
            // 休憩データがない場合はスキップ
            if (empty($breaking->start_time) || empty($breaking->end_time)) {
                continue;
            }

            $start = Carbon::parse($breaking->start_time);
            $end   = Carbon::parse($breaking->end_time);

            $res = $res + $start->diffInSeconds($end);
        }

        return $res;
    }

    /**
     * 実勤務時間を算出する
     *
     * @return int
     */
    public function actualWorkingTime(string $workEndTime, int $totalBreakingTime): int
    {
        $res = 0;

        $start = Carbon::parse($this->start_time);
        $end   = Carbon::parse($workEndTime);
        $totalWorkTime = $start->diffInSeconds($end);
        $res = $totalWorkTime - $totalBreakingTime;

        return $res;
    }
}
