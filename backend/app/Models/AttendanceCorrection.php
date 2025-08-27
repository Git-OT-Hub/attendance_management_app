<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
