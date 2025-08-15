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
     * 勤務状態を確認し、その結果を JSON形式で返す
     *
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function state(Request $request): JsonResponse
    {
        $startTime = (string)$request->query('start_time');

        $res = $this->attendanceService->workingState($startTime);

        if (!$res) {
            return response()->json([
                'state' => '勤務外'
            ], Response::HTTP_OK);
        }

        return response()->json($res, Response::HTTP_OK);
    }

    /**
     * 出勤処理を行い、その結果を JSON形式で返す
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
