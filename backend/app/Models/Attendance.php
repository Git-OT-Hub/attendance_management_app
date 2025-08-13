<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'start_time',
        'end_time',
        'total_breaking_time',
        'total_working_time',
        'corrected_start_time',
        'corrected_end_time',
        'comment',
        'is_correction_request',
        'correction_request_date',
        'is_approval',
        'approval_date',
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
}
