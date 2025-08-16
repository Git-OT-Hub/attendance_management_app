"use client";
import { useState, useLayoutEffect } from "react";
import { apiClient } from "@/lib/axios/axios";
import { AxiosResponse } from "axios";
import { formatDateTime } from "@/lib/dateTime/date";
import type { AttendanceType, WorkingStateType, BreakingType, FinishBreakingType } from "@/types/attendance/attendance";
import { HTTP_OK ,HTTP_CREATED } from "@/constants/httpStatus";
import { flashStore } from "@/store/zustand/flashStore";
import { userStore } from "@/store/zustand/userStore";
import AttendanceState from "@/components/attendance/attendanceState/AttendanceState";
import AttendanceButton from "@/components/attendance/attendanceButton/AttendanceButton";
import styles from "@/components/attendance/attendanceClient/AttendanceClient.module.scss";
import { WORK, BREAK, FINISHED } from "@/constants/attendanceState";

const AttendanceClient = ({
	children,
}: Readonly<{
	children: React.ReactNode;
}>) => {
    const [workingState, setWorkingState] = useState<WorkingStateType>("勤務外");
    const { createFlash } = flashStore();
    const { loginUserId } = userStore();

    useLayoutEffect(() => {
        let startTime = "";
        const savedDateTime = localStorage.getItem(`dateTime_${loginUserId}`);

        // 出勤処理後は、ローカルストレージに保存された日時を使う
        // 理由：出勤処理後、日付が変わっても、休憩処理、退勤処理を正常に行えるようにするため
        if (savedDateTime) {
            startTime = savedDateTime;
        } else {
            startTime = formatDateTime();
        }
        console.log(startTime);

        apiClient.get(`/api/attendance/state?start_time=${encodeURIComponent(startTime)}`)
            .then((res) => {
                console.log('useEffect: ', res);
                if (res.status === HTTP_OK && res.data.state === "勤務外") {
                    setWorkingState(res.data.state);
                    return;
                }

                if (res.status === HTTP_OK && res.data.state) {
                    setWorkingState(res.data.state);
                    return;
                }
            })
            .catch((e) => {
                createFlash({
                    type: "error",
                    message: "勤務状態の確認処理に失敗しました"
                });
                console.error('予期しないエラー: ', e);
            });
    }, []);

    const startWorking = () => {
        if (confirm("この操作は、取り消しできませんがよろしいですか？")) {
            const startTime = formatDateTime();
            const data = {
                start_time: startTime,
                state: WORK,
            };

            // ローカルストレージに出勤日時を保存
            // useLayoutEffectで使用
            localStorage.setItem(`dateTime_${loginUserId}`, startTime);

            apiClient.post('/api/attendance/work', data).then((res: AxiosResponse<AttendanceType>) => {
                console.log('startWorking: ', res);
                if (res.status !== HTTP_CREATED) {
                    console.error('予期しないエラー: ', res.status);
                    return;
                }

                if (res.data.id) {
                    // 休憩処理、退勤処理に使用
                    localStorage.setItem(`attendance_id_${loginUserId}`, String(res.data.id));
                }

                if (res.data.state === "出勤中") {
                    setWorkingState(res.data.state);
                }

                createFlash({
                    type: "success",
                    message: "出勤しました"
                });
            }).catch((e) => {
                createFlash({
                    type: "error",
                    message: "出勤の処理に失敗しました"
                });
                console.error('予期しないエラー: ', e);
            });
        }
    };

    const leaveWork = () => {

    };

    const takeBreak = () => {
        if (confirm("休憩に入りますか？")) {
            const breakStartTime = formatDateTime();
            const attendanceId = localStorage.getItem(`attendance_id_${loginUserId}`);
            const data = {
                attendance_id: Number(attendanceId),
                start_time: breakStartTime,
                state: BREAK,
            };

            apiClient.post('/api/attendance/break', data)
                .then((res: AxiosResponse<BreakingType>) => {
                    console.log('takeBreak: ', res);
                    if (res.status !== HTTP_CREATED) {
                        console.error('予期しないエラー: ', res.status);
                        return;
                    }

                    if (res.data.breaking_id) {
                        // 休憩終了処理に使用
                        localStorage.setItem(`breaking_id_${loginUserId}`, String(res.data.breaking_id));
                    }

                    if (res.data.state === "休憩中") {
                        setWorkingState(res.data.state);
                    }

                    createFlash({
                        type: "success",
                        message: "休憩を開始しました"
                    });
                }).catch((e) => {
                    createFlash({
                        type: "error",
                        message: "休憩開始処理に失敗しました"
                    });
                    console.error('予期しないエラー: ', e);
                });
        }
    };

    const finishBreak = () => {
        if (confirm("休憩を終了しますか？")) {
            const finishBreakTime = formatDateTime();
            const attendanceId = localStorage.getItem(`attendance_id_${loginUserId}`);
            const breakingId = localStorage.getItem(`breaking_id_${loginUserId}`);
            const data = {
                attendance_id: Number(attendanceId),
                breaking_id: Number(breakingId),
                end_time: finishBreakTime,
                state: WORK,
            };

            apiClient.patch('/api/attendance/finish_break', data)
                .then((res: AxiosResponse<FinishBreakingType>) => {
                    console.log('finishBreak: ', res);
                    if (res.status !== HTTP_OK) {
                        console.error('予期しないエラー: ', res.status);
                        return;
                    }

                    if (res.data.state === "出勤中") {
                        localStorage.removeItem(`breaking_id_${loginUserId}`);
                        setWorkingState(res.data.state);
                    }

                    createFlash({
                        type: "success",
                        message: "休憩を終了しました"
                    });
                }).catch((e) => {
                    createFlash({
                        type: "error",
                        message: "休憩終了処理に失敗しました"
                    });
                    console.error('予期しないエラー: ', e);
                });
        };
    };

    return (
        <div>
            <div className={styles.state}>
                <AttendanceState
                    text={workingState}
                />
            </div>
            {children}
            <div className={styles.btnArea}>
                {workingState === "勤務外" && (
                    <AttendanceButton
                        text="出勤"
                        fn={startWorking}
                    />
                )}

                {workingState === "出勤中" && (
                    <div className={styles.multipleBtn}>
                        <AttendanceButton
                            text="退勤"
                            fn={leaveWork}
                        />
                        <AttendanceButton
                            text="休憩入"
                            fn={takeBreak}
                        />
                    </div>
                )}

                {workingState === "休憩中" && (
                    <AttendanceButton
                        text="休憩戻"
                        fn={finishBreak}
                    />
                )}
            </div>
        </div>
    )
}

export default AttendanceClient