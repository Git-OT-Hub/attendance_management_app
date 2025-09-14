<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Controller;
use App\Services\Contracts\Admin\AttendanceServiceInterface;
use App\Http\Requests\Admin\Attendance\AttendanceCreateRequest;
use App\Http\Requests\Admin\Attendance\AttendanceCorrectionRequest;

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

    /**
     * 勤怠新規登録を行い、その結果を JSON形式で返す
     *
     * @param  AttendanceCreateRequest $request
     * @return JsonResponse
     */
    public function create(AttendanceCreateRequest $request): JsonResponse
    {
        $res = $this->attendanceService->createAttendance($request);

        if (!$res) {
            return response()->json([
                'message' => '勤怠の新規登録に失敗しました'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json($res, Response::HTTP_CREATED);
    }

    /**
     * 一般ユーザーの勤怠における詳細情報を取得し、その結果を JSON形式で返す
     *
     * @param  string $id
     * @return JsonResponse
    */
    public function show(string $id): JsonResponse
    {
        $res = $this->attendanceService->attendanceShow($id);

        if (!$res) {
            return response()->json([
                'message' => '勤怠詳細の取得に失敗しました'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json($res, Response::HTTP_OK);
    }

    /**
     * 勤怠修正処理を行い、その結果を JSON形式で返す
     *
     * @param AttendanceCorrectionRequest $request
     * @return JsonResponse
    */
    public function correction(AttendanceCorrectionRequest $request): JsonResponse
    {
        $res = $this->attendanceService->correctAttendance($request);

        if (!$res) {
            return response()->json([
                'message' => '勤怠修正処理に失敗しました'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json($res, Response::HTTP_OK);
    }

    /**
     * 勤怠修正申請の承認処理を行い、その結果を JSON形式で返す
     *
     * @param  Request $request
     * @return JsonResponse
    */
    public function approve(Request $request): JsonResponse
    {
        $res = $this->attendanceService->approveAttendance($request);

        if (!$res) {
            return response()->json([
                'message' => '承認処理に失敗しました'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json($res, Response::HTTP_OK);
    }

    /**
     * 承認待ちの申請一覧結果を JSON形式で返す
     *
     * @return JsonResponse
    */
    public function waitingList(): JsonResponse
    {
        $res = $this->attendanceService->attendanceWaitingList();

        if ($res === null) {
            return response()->json([
                'message' => '承認待ち申請一覧の取得に失敗しました'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json($res, Response::HTTP_OK);
    }

    /**
     * 承認済みの申請一覧結果を JSON形式で返す
     *
     * @return JsonResponse
    */
    public function approvedList(): JsonResponse
    {
        $res = $this->attendanceService->attendanceApprovedList();

        if ($res === null) {
            return response()->json([
                'message' => '承認済み申請一覧の取得に失敗しました'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json($res, Response::HTTP_OK);
    }
}
