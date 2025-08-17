"use client";
import styles from "@/components/attendanceList/attendanceListClient/AttendanceListClient.module.scss";
import { useState, useEffect } from "react";
import dayjs from "dayjs";
import { apiClient } from "@/lib/axios/axios";
import { AxiosResponse } from "axios";
import type { AttendanceListType } from "@/types/attendance/attendance";
import { HTTP_OK } from "@/constants/httpStatus";

const AttendanceListClient = () => {
    const [date, setDate] = useState<string>(dayjs().format("YYYY-MM"));
    const [records, setRecords] = useState<AttendanceListType[]>([]);

    useEffect(() => {
        apiClient.get(`/api/attendance/list?date=${date}`)
            .then((res: AxiosResponse<AttendanceListType[]>) => {
                if (res.status !== HTTP_OK) {
                    console.error('予期しないエラー: ', res.status);
                    return;
                }

                setRecords(res.data);
            })
            .catch((e) => {
                console.error('予期しないエラー: ', e);
            });
    }, [date]);
    console.log(records);

    const previousMonth = () => {
        setDate(dayjs(date).subtract(1, "month").format("YYYY-MM"));
    };

    const nextMonth = () => {
        setDate(dayjs(date).add(1, "month").format("YYYY-MM"));
    };

    return (
        <div>
            <div>
                <button onClick={previousMonth}>前月</button>
                <h2>{dayjs(date).format("YYYY/MM")}</h2>
                <button onClick={nextMonth}>翌月</button>
            </div>

            <table>
                
            </table>
        </div>
    )
}

export default AttendanceListClient