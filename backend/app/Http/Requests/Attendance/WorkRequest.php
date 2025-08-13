<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class WorkRequest extends FormRequest
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
            'start_time.required' => '出勤日時の情報は必須です',
            'start_time.date_format' => '出勤日時のフォーマットが正しくありません',

            'state.required' => '勤怠状態は必須です',
            'state.integer' => '勤怠状態は整数である必要があります',
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
