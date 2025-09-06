<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Breaking;
use Carbon\Carbon;

class FinishBreakingRequest extends FormRequest
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
            'breaking_id' => ['required', 'integer'],
            'end_time' => ['required', 'date_format:Y-m-d H:i:s'],
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

            'breaking_id.required' => '休憩idは必須です',
            'breaking_id.integer' => '休憩idは整数である必要があります',

            'end_time.required' => '休憩終了日時の情報は必須です',
            'end_time.date_format' => '休憩終了日時のフォーマットが正しくありません',

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
            $breakingId = $this->input('breaking_id', null);
            $breakingEndTime = $this->input('end_time', "");
            $breaking = Breaking::find($breakingId);

            if (!$breaking->start_time || !$breakingEndTime) {
                return;
            }

            $breakingStart = Carbon::parse($breaking->start_time);
            $breakingEnd = Carbon::parse($breakingEndTime);

            // 休憩開始時間と休憩終了時間が同じでないか
            if ($breakingEnd->lessThanOrEqualTo($breakingStart)) {
                $validator->errors()->add('end_time', '休憩開始時間 と 休憩終了時間は 1分以上 あけてください');
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
