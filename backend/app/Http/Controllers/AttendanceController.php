<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Contracts\AttendanceServiceInterface;
use App\Http\Requests\Attendance\WorkRequest;
use App\Http\Requests\Attendance\BreakingRequest;
use App\Http\Requests\Attendance\FinishBreakingRequest;
use App\Http\Requests\Attendance\FinishWorkRequest;
use App\Http\Requests\Attendance\AttendanceCorrectionRequest;
use App\Models\Attendance;

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
     * @param \Illuminate\Http\Request $request
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

    /**
     * 休憩開始処理を行い、その結果を JSON形式で返す
     *
     * @param \App\Http\Requests\Attendance\BreakingRequest $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function break(BreakingRequest $request): JsonResponse
    {
        $res = $this->attendanceService->startBreak($request);

        if (!$res) {
            return response()->json([
                'message' => '休憩開始処理に失敗しました'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json($res, Response::HTTP_CREATED);
    }

    /**
     * 休憩終了処理を行い、その結果を JSON形式で返す
     *
     * @param \App\Http\Requests\Attendance\FinishBreakingRequest $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function finishBreak(FinishBreakingRequest $request): JsonResponse
    {
        $res = $this->attendanceService->breakEnd($request);

        if (!$res) {
            return response()->json([
                'message' => '休憩終了処理に失敗しました'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json($res, Response::HTTP_OK);
    }

    /**
     * 退勤処理を行い、その結果を JSON形式で返す
     *
     * @param \App\Http\Requests\Attendance\FinishWorkRequest $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function finishWork(FinishWorkRequest $request): JsonResponse
    {
        $res = $this->attendanceService->clockOut($request);

        if (!$res) {
            return response()->json([
                'message' => '退勤処理に失敗しました'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json($res, Response::HTTP_OK);
    }

    /**
     * 対象月の日付リストを生成し、その結果を JSON形式で返す
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function list(Request $request): JsonResponse
    {
        $date = (string)$request->query('month') . '-01';

        $res = $this->attendanceService->attendanceList($date);

        if (!$res) {
            return response()->json([
                'message' => '勤怠一覧の取得に失敗しました'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json($res, Response::HTTP_OK);
    }

    /**
     * ログインユーザーの勤怠における詳細情報を取得し、その結果を JSON形式で返す
     *
     * @param  string $id
     * @return \Illuminate\Http\JsonResponse
    */
    public function show(string $id): JsonResponse
    {
        // ポリシーの呼び出し
        $attendance = Attendance::find($id);
        $this->authorize('view', $attendance);

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
     * @param \App\Http\Requests\Attendance\AttendanceCorrectionRequest $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function correction(AttendanceCorrectionRequest $request): JsonResponse
    {
        // $res = $this->attendanceService->attendanceCorrection($request);

        // if (!$res) {
        //     return response()->json([
        //         'message' => '勤怠修正処理に失敗しました'
        //     ], Response::HTTP_INTERNAL_SERVER_ERROR);
        // }

        // return response()->json($res, Response::HTTP_OK);

        return response()->json("success!!", Response::HTTP_OK);
    }
}
