"use client";
import styles from "@/components/admin/attendanceList/attendanceListClient/AttendanceListClient.module.scss";
import Link from "next/link";
import { FaArrowLeft, FaArrowRight } from "react-icons/fa6";
import { FaRegCalendarAlt } from "react-icons/fa";
import { useState, useEffect } from "react";
import { apiClient } from "@/lib/axios/axios";
import { AxiosResponse } from "axios";
import type { AdminAttendanceListType } from "@/types/attendance/attendance";
import { HTTP_OK } from "@/constants/httpStatus";
import { formatWithDayjs, subtractWithDayjs, addWithDayjs } from "@/lib/dateTime/date";

const AttendanceListClient = () => {
    const [date, setDate] = useState<string>(formatWithDayjs({ format: "YYYY-MM-DD" }));
    const [records, setRecords] = useState<AdminAttendanceListType[]>([]);

    useEffect(() => {
        apiClient.get(`/api/admin/attendance/list?date=${date}`)
            .then((res: AxiosResponse<AdminAttendanceListType[]>) => {
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

    const previousDate = () => {
        setDate(subtractWithDayjs({
            day: date,
            num: 1,
            unit: "day",
            format: "YYYY-MM-DD"
        }));
    };

    const nextDate = () => {
        setDate(addWithDayjs({
            day: date,
            num: 1,
            unit: "day",
            format: "YYYY-MM-DD"
        }));
    };

    return (
        <div className={styles.content}>
            <h1>
                {formatWithDayjs({
                    day: date,
                    format: "YYYY年M月D日"
                })}の勤怠
            </h1>

            <div className={styles.header}>
                <div
                    className={styles.left}
                    onClick={previousDate}
                >
                    <FaArrowLeft className={styles.customArrow} />
                    <span>前日</span>
                </div>
                <div className={styles.center}>
                    <FaRegCalendarAlt className={styles.customCalendar} />
                    <span>
                        {formatWithDayjs({ day: date, format: "YYYY/MM/DD" })}
                    </span>
                </div>
                <div
                    className={styles.right}
                    onClick={nextDate}
                >
                    <span>翌日</span>
                    <FaArrowRight className={styles.customArrow} />
                </div>
            </div>

            <table className={styles.table}>
                <thead className={styles.tableHead}>
                    <tr>
                        <th>名前</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody className={styles.tableBody}>
                    {records.map((record, idx) => (
                        <tr key={idx}>
                            <td>{record.user_name}</td>
                            <td>{record.start_time ? record.start_time : ""}</td>
                            <td>{record.end_time ? record.end_time : ""}</td>
                            <td>{record.total_breaking_time ? record.total_breaking_time : ""}</td>
                            <td>{record.actual_working_time ? record.actual_working_time : ""}</td>
                            <td>
                                <Link
                                    className={styles.tableLink}
                                    href={record.id ? `/admin/attendance/${record.id}`
                                    : {
                                        pathname: "/admin/attendance/create",
                                        query: {
                                            yearMonth: date,
                                            userId: record.user_id,
                                            userName: record.user_name,
                                        },
                                    }}
                                >詳細</Link>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    )
}

export default AttendanceListClient