<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * ユーザーに紐づく勤怠情報を取得するリレーション
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Attendance>
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * ユーザーに紐づく勤怠修正履歴を取得するリレーション
     *
     * @return HasManyThrough<\App\Models\AttendanceCorrection>
     */
    public function attendanceCorrections(): HasManyThrough
    {
        return $this->HasManyThrough(
            AttendanceCorrection::class,
            Attendance::class,
            'user_id',
            'attendance_id',
            'id',
            'id'
        );
    }
}
