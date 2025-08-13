<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Contracts\AttendanceServiceInterface;
use App\Http\Requests\Attendance\WorkRequest;

class AttendanceController extends Controller
{
    private AttendanceServiceInterface $attendanceService;

    public function __construct(AttendanceServiceInterface $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * 出勤処理を行い、その結果を JSON 形式で返す
     *
     * @param \App\Http\Requests\Attendance\WorkRequest $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function work(WorkRequest $request): JsonResponse
    {
        $res = $this->attendanceService->startWorking($request);

        if (!$res) {
            return response()->json([
                'message' => '同じ日付の出勤記録が既に存在します。'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json($res, Response::HTTP_CREATED);
    }
}
