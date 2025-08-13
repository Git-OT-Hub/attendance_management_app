"use client";
import { useState } from "react";
import { apiClient } from "@/lib/axios/axios";
import { AxiosResponse } from "axios";
import { formatDateTime } from "@/lib/dateTime/date";
import type { AttendanceType } from "@/types/attendance/attendance";
import { HTTP_CREATED } from "@/constants/httpStatus";
import { flashStore } from "@/store/zustand/flashStore";

const AttendanceClient = ({
	children,
}: Readonly<{
	children: React.ReactNode;
}>) => {
    const { createFlash } = flashStore();

    const startWorking = () => {
        if (confirm("この操作は、取り消しできませんがよろしいですか？")) {
            const data = {
                start_time: formatDateTime(),
                state: 1,
            };

            apiClient.post('/api/attendance/work', data).then((res: AxiosResponse<AttendanceType>) => {
                if (res.status !== HTTP_CREATED) {
                    console.error('予期しないエラー: ', res.status);
                    return;
                }

                createFlash({
                    type: "success",
                    message: "出勤しました"
                });
                console.log('成功: ', res);
            }).catch((e) => {
                createFlash({
                    type: "error",
                    message: "出勤の処理に失敗しました"
                });
                console.error('予期しないエラー: ', e);
            });
        }
    };

    return (
        <div>
            <div>
                勤務外
            </div>
            {children}
            <div>
                <button onClick={startWorking}>出勤</button>
            </div>
        </div>
    )
}

export default AttendanceClient