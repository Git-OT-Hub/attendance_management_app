<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class AttendanceCreateRequest extends FormRequest
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
            'attendance.attendance_id' => ['nullable', 'integer'],
            'attendance.attendance_start_date' => ['required', 'date'],
            'attendance.attendance_start_time' => ['required', 'date'],
            'attendance.attendance_end_time' => ['required', 'date'],

            // コメント
            'comment' => ['required', 'string', 'max:255'],

            // 休憩
            'breaking.breaking_start_time' => ['nullable', 'required_with:breaking.breaking_end_time', 'date'],
            'breaking.breaking_end_time'     => ['nullable', 'required_with:breaking.breaking_start_time', 'date'],

            // 修正依頼日
            'correction_request_date' => ['required', 'date'],
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
            $attendance = $this->input('attendance', []);
            $breaking  = $this->input('breaking', []);

            if (!isset(
                $attendance['attendance_start_date'],
                $attendance['attendance_start_time'],
                $attendance['attendance_end_time']
            )) {
                return;
            }

            $startDate = Carbon::parse($attendance['attendance_start_date'])->format('Y-m-d');
            $start = Carbon::parse($attendance['attendance_start_time']);
            $end   = Carbon::parse($attendance['attendance_end_time']);

            // 出勤日と出勤時間の年月日が一致しているか
            if ($startDate !== $start->format('Y-m-d')) {
                $validator->errors()->add(
                    'attendance.attendance_start_time',
                    '日付 と 出勤時間 の年月日が一致している必要があります。'
                );
            }

            // 出勤 が 退勤 より前、及び、退勤 が 出勤 より後になっているか
            if ($start->greaterThanOrEqualTo($end)) {
                $validator->errors()->add('attendance.attendance_start_time', '出勤時間 は 退勤時間 より 前 である必要があります。');
                $validator->errors()->add('attendance.attendance_end_time', '退勤時間 は 出勤時間 より 後 である必要があります。');
            }

            // 休憩のチェック
            if (!isset($breaking['breaking_start_time'], $breaking['breaking_end_time'])) {
                return;
            }

            $bStart = Carbon::parse($breaking['breaking_start_time']);
            $bEnd   = Carbon::parse($breaking['breaking_end_time']);

            // 休憩開始が 出勤 と 退勤 の間にあるか
            if ($bStart->lessThanOrEqualTo($start) || $bStart->greaterThanOrEqualTo($end)) {
                $validator->errors()->add("breaking.breaking_start_time", "休憩開始時間 は 出勤 〜 退勤 の 間 である必要があります。");
            }

            // 休憩終了が 休憩開始 と 退勤 の間にあるか
            if ($bEnd->lessThanOrEqualTo($bStart) || $bEnd->greaterThanOrEqualTo($end)) {
                $validator->errors()->add("breaking.breaking_end_time", "休憩終了時間 は 休憩開始 〜 退勤 の 間 である必要があります。");
            }
        });
    }

    /**
     * バリデーションエラーの日本語化
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            // 出勤・退勤
            'attendance.attendance_id.integer'  => '勤怠データIDは整数である必要があります。',
            'attendance.attendance_start_time.required' => '出勤時間 は必須です。',
            'attendance.attendance_start_time.date'     => '出勤時間 は正しい日付形式で入力してください。',
            'attendance.attendance_end_time.required'   => '退勤時間 は必須です。',
            'attendance.attendance_end_time.date'       => '退勤時間 は正しい日付形式で入力してください。',
            'attendance.attendance_start_date.required' => '出勤日 は必須です。',
            'attendance.attendance_start_date.date'     => '出勤日 は正しい日付形式で入力してください。',

            // コメント
            'comment.required' => '備考 は必須です。',
            'comment.string'   => '備考 は文字列で入力してください。',
            'comment.max'      => '備考 は255文字以内で入力してください。',

            // 休憩
            'breaking.breaking_start_time.required_with' => '休憩終了時間 がある場合は 休憩開始時間 を入力してください。',
            'breaking.breaking_start_time.date'         => '休憩開始時間 は正しい日付形式で入力してください。',
            'breaking.breaking_end_time.required_with'  => '休憩開始時間 がある場合は 休憩終了時間 を入力してください。',
            'breaking.breaking_end_time.date'           => '休憩終了時間 は正しい日付形式で入力してください。',

            // 修正依頼日
            'correction_request_date.required' => '修正依頼日 は必須です。',
            'correction_request_date.date' => '修正依頼日 は正しい日付形式で入力してください。',
        ];
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
