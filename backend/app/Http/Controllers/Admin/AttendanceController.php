<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Controller;
use App\Services\Contracts\Admin\AttendanceServiceInterface;

class AttendanceController extends Controller
{
    private AttendanceServiceInterface $attendanceService;

    public function __construct(AttendanceServiceInterface $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * 対象日の一般ユーザー勤怠情報リストを取得し、JSON形式で返す
     *
     * @param Request $request
     * @return JsonResponse
    */
    public function todayList(Request $request): JsonResponse
    {
        $date = $request->query('date');

        $res = $this->attendanceService->attendanceTodayList($date);

        if (!$res) {
            return response()->json([
                'message' => '一般ユーザー勤怠情報リストの取得に失敗しました'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json($res, Response::HTTP_OK);
    }
}
