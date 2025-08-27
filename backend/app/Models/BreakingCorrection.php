<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BreakingCorrection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'attendance_correction_id',
        'start_time',
        'end_time',
    ];

    /**
     * 休憩修正情報に紐づく勤怠修正情報を取得するリレーション
     *
     * @return BelongsTo<\App\Models\AttendanceCorrection>
     */
    public function attendanceCorrection(): BelongsTo
    {
        return $this->belongsTo(AttendanceCorrection::class);
    }
}
