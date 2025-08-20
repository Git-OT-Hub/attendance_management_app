"use client";
import styles from "@/components/attendanceList/attendanceListClient/AttendanceListClient.module.scss";
import { FaArrowLeft, FaArrowRight } from "react-icons/fa6";
import { FaRegCalendarAlt } from "react-icons/fa";
import { useState, useEffect } from "react";
import { apiClient } from "@/lib/axios/axios";
import { AxiosResponse } from "axios";
import type { AttendanceListType } from "@/types/attendance/attendance";
import { HTTP_OK } from "@/constants/httpStatus";
import { formatWithDayjs, subtractWithDayjs, addWithDayjs } from "@/lib/dateTime/date";

const AttendanceListClient = () => {
    const [month, setMonth] = useState<string>(formatWithDayjs({ format: "YYYY-MM" }));
    const [records, setRecords] = useState<AttendanceListType[]>([]);

    useEffect(() => {
        apiClient.get(`/api/attendance/list?month=${month}`)
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
    }, [month]);

    console.log(records);

    const previousMonth = () => {
        setMonth(subtractWithDayjs({
            day: month,
            num: 1,
            unit: "month",
            format: "YYYY-MM"
        }));
    };

    const nextMonth = () => {
        setMonth(addWithDayjs({
            day: month,
            num: 1,
            unit: "month",
            format: "YYYY-MM"
        }));
    };

    return (
        <div className={styles.content}>
            <div className={styles.header}>
                <div
                    className={styles.left}
                    onClick={previousMonth}
                >
                    <FaArrowLeft className={styles.customArrow} />
                    <span>前月</span>
                </div>
                <div className={styles.center}>
                    <FaRegCalendarAlt className={styles.customCalendar} />
                    <span>
                        {formatWithDayjs({ day: month, format: "YYYY/MM" })}
                    </span>
                </div>
                <div
                    className={styles.right}
                    onClick={nextMonth}
                >
                    <span>翌月</span>
                    <FaArrowRight className={styles.customArrow} />
                </div>
            </div>

            <table>
                
            </table>
        </div>
    )
}

export default AttendanceListClient