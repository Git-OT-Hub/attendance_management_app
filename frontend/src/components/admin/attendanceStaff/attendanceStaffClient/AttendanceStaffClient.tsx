"use client";
import styles from "@/components/admin/attendanceStaff/attendanceStaffClient/AttendanceStaffClient.module.scss";
import type { AttendanceStaffClientProps } from "@/types/attendance/attendance";
import Link from "next/link";
import { useSearchParams, useRouter } from "next/navigation";
import { FaArrowLeft, FaArrowRight } from "react-icons/fa6";
import { FaRegCalendarAlt } from "react-icons/fa";
import { useState, useEffect } from "react";
import { apiClient } from "@/lib/axios/axios";
import { AxiosResponse } from "axios";
import type { AttendanceStaffClientType } from "@/types/attendance/attendance";
import { HTTP_OK } from "@/constants/httpStatus";
import { formatWithDayjs, subtractWithDayjs, addWithDayjs } from "@/lib/dateTime/date";
import { flashStore } from "@/store/zustand/flashStore";

const AttendanceStaffClient = ({id}: AttendanceStaffClientProps) => {
    const searchParams = useSearchParams();
    const userName = searchParams.get("userName");
    const router = useRouter();
    const { createFlash } = flashStore();
    const [month, setMonth] = useState<string>(formatWithDayjs({ format: "YYYY-MM" }));
    const [records, setRecords] = useState<AttendanceStaffClientType[]>([]);

    if (!userName) {
        createFlash({
            type: "error",
            message: "詳細ボタンから移動してください"
        });
        router.push('/admin/staff/list');

        return;
    }

    useEffect(() => {
        apiClient.get(`/api/admin/attendance/monthly/list?month=${month}&userId=${id}`)
            .then((res: AxiosResponse<AttendanceStaffClientType[]>) => {
                if (res.status !== HTTP_OK) {
                    console.error('予期しないエラー: ', res.status);
                    return;
                }

                setRecords(res.data);
            })
            .catch((e) => {
                console.error('予期しないエラー: ', e);
            });
    }, [month, id]);

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
            <h1>{userName} さんの勤怠</h1>
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

            <table className={styles.table}>
                <thead className={styles.tableHead}>
                    <tr>
                        <th>日付</th>
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
                            <td>{record.date}</td>
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
                                            yearMonth: record.year_month,
                                            userId: id,
                                            userName: userName,
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

export default AttendanceStaffClient