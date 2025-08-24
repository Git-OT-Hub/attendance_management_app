<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCorrectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 出勤・退勤
            'attendance.attendance_id' => ['required', 'integer'],
            'attendance.attendance_start_time' => ['required', 'date'],
            'attendance.attendance_end_time' => ['required', 'date'],

            // コメント
            'comment' => ['required', 'string', 'max:255'],

            // 休憩
            'breakings' => ['array'],
            'breakings.*.breaking_id' => ['nullable', 'integer'],
            'breakings.*.breaking_start_time' => ['nullable', 'required_with:breakings.*.breaking_end_time', 'date'],
            'breakings.*.breaking_end_time'     => ['nullable', 'required_with:breakings.*.breaking_start_time', 'date'],
        ];
    }
}
