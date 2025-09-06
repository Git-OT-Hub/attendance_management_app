<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Attendance;
use Carbon\Carbon;

class BreakingRequest extends FormRequest
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
            'attendance_id' => ['required', 'integer'],
            'start_time' => ['required', 'date_format:Y-m-d H:i:s'],
            'state' => ['required', 'integer'],
        ];
    }

    /**
     * バリデーションエラーの日本語化
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'attendance_id.required' => '勤怠idは必須です',
            'attendance_id.integer' => '勤怠idは整数である必要があります',

            'start_time.required' => '休憩開始日時の情報は必須です',
            'start_time.date_format' => '休憩開始日時のフォーマットが正しくありません',

            'state.required' => '勤怠状態は必須です',
            'state.integer' => '勤怠状態は整数である必要があります',
        ];
    }

    /**
     * 追加のバリデーション
     *
     * @param Validator $validator
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $attendanceId = $this->input('attendance_id', null);
            $breakingStartTime = $this->input('start_time', "");
            $attendance = Attendance::find($attendanceId);
            $latestBreaking = $attendance->breakings()->orderByDesc('id')->first();
            $latestBreakingEndTime = $latestBreaking?->end_time;

            if (!$attendance->start_time || !$breakingStartTime) {
                return;
            }

            $attendanceStart = Carbon::parse($attendance->start_time);
            $breakingStart = Carbon::parse($breakingStartTime);

            // 出勤時間と休憩開始時間が同じでないか
            if ($breakingStart->lessThanOrEqualTo($attendanceStart)) {
                $validator->errors()->add('start_time', '出勤時間 と 休憩開始時間は 1分以上 あけてください');
            }

            if (!$latestBreakingEndTime) {
                return;
            }

            $latestBreakingEnd = Carbon::parse($latestBreakingEndTime);

            // 前回の休憩終了時間と今回の休憩開始時間が同じでないか
            if ($breakingStart->lessThanOrEqualTo($latestBreakingEnd)) {
                $validator->errors()->add('start_time', '前回の休憩終了時間から 1分以上 あけてください');
            }
        });
    }

    /**
     * APIのバリデーションチェックに失敗した際、JSONでエラーレスポンスを返す
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @return void
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        $response = [
            'errors' => $validator->errors()->toArray(),
        ];

        throw new HttpResponseException(
            response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
