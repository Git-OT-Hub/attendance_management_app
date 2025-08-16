<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Breaking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'attendance_id',
        'start_time',
        'end_time',
        'corrected_start_time',
        'corrected_end_time',
        'is_correction_request',
        'is_approval',
    ];

    /**
     * 休憩に紐づく勤怠を取得するリレーション
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Attendance>
     */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }
}
