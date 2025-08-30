<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AttendanceCorrection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'attendance_id',
        'start_date',
        'start_time',
        'end_time',
        'total_breaking_time',
        'actual_working_time',
        'comment',
        'correction_request_date',
        'approval_date',
        'state',
    ];

    /**
     * 勤怠修正情報に紐づく勤怠情報を取得するリレーション
     *
     * @return BelongsTo<\App\Models\Attendance>
     */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * 勤怠修正情報に紐づく休憩修正情報を取得するリレーション
     *
     * @return HasMany<\App\Models\BreakingCorrection>
     */
    public function breakingCorrections(): HasMany
    {
        return $this->hasMany(BreakingCorrection::class);
    }

    /**
     * 休憩時間の合計を算出する
     *
     * @return int
     */
    public function totalBreakingTime(): int
    {
        $res = 0;
        $breakings = $this->breakingCorrections;

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
    public function actualWorkingTime(int $totalBreakingTime): int
    {
        $res = 0;

        $start = Carbon::parse($this->start_time);
        $end   = Carbon::parse($this->end_time);
        $totalWorkTime = $start->diffInSeconds($end);
        $res = $totalWorkTime - $totalBreakingTime;

        return $res;
    }
}
