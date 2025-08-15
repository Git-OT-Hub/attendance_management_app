"use client";
import { useState, useLayoutEffect } from "react";
import { apiClient } from "@/lib/axios/axios";
import { AxiosResponse } from "axios";
import { formatDateTime } from "@/lib/dateTime/date";
import type { AttendanceType, WorkingStateType } from "@/types/attendance/attendance";
import { HTTP_OK ,HTTP_CREATED } from "@/constants/httpStatus";
import { flashStore } from "@/store/zustand/flashStore";
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

    useLayoutEffect(() => {
        const startTime = formatDateTime();

        apiClient.get(`/api/attendance/state?start_time=${encodeURIComponent(startTime)}`)
            .then((res) => {
                console.log(res);
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
            const data = {
                start_time: formatDateTime(),
                state: WORK,
            };

            apiClient.post('/api/attendance/work', data).then((res: AxiosResponse<AttendanceType>) => {
                if (res.status !== HTTP_CREATED) {
                    console.error('予期しないエラー: ', res.status);
                    return;
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
            </div>
        </div>
    )
}

export default AttendanceClient