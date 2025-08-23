"use client";
import styles from "@/components/attendanceShow/attendanceShowClient/AttendanceShowClient.module.scss";
import type { AttendanceShowClientProps } from "@/types/attendance/attendance";
import { useLayoutEffect, useState } from "react";
import { useSearchParams, useRouter } from "next/navigation";
import { apiClient } from "@/lib/axios/axios";
import { AxiosResponse } from "axios";
import type { AttendanceShowType } from "@/types/attendance/attendance";
import { HTTP_OK, HTTP_FORBIDDEN } from "@/constants/httpStatus";
import { userStore } from "@/store/zustand/userStore";
import { formatWithDayjs } from "@/lib/dateTime/date";
import { flashStore } from "@/store/zustand/flashStore";
import Loading from "@/components/ui/loading/Loading";

const AttendanceShowClient = ({id}: AttendanceShowClientProps) => {
    const [loading, setLoading] = useState<boolean>(true);
    const { user } = userStore();
    const searchParams = useSearchParams();
    const yearMonth = searchParams.get('year_month');
    const { createFlash } = flashStore();
    const router = useRouter();
    const [attendanceStartTime, setAttendanceStartTime] = useState<string>('');
    const [attendanceEndTime, setAttendanceEndTime] = useState<string>('');

    if (!yearMonth) {
        createFlash({
            type: "error",
            message: "詳細ボタンから勤怠詳細画面を開いてください"
        });
        router.push('/attendance/list');
    }

    useLayoutEffect(() => {
        apiClient.get(`/api/attendance/${id}`)
            .then((res: AxiosResponse<AttendanceShowType>) => {
                if (res.status !== HTTP_OK) {
                    console.error('予期しないエラー: ', res.status);
                    return;
                }

                console.log(res);

                setAttendanceStartTime(res.data.attendance_start_time);
                if (res.data.attendance_end_time) {
                    setAttendanceEndTime(res.data.attendance_end_time);
                }

                setLoading(false);
            })
            .catch((e) => {
                if (e.status === HTTP_FORBIDDEN) {
                    createFlash({
                        type: "error",
                        message: "コンテンツが見つかりませんでした"
                    });
                    router.push('/attendance/list');

                    return;
                }

                console.error('予期しないエラー: ', e);
                createFlash({
                    type: "error",
                    message: "エラーが発生しました。"
                });
                router.push('/attendance/list');
            });
    }, [id]);

    const correction = () => {

    };

    if (loading) {
        return (
            <Loading />
        )
    }

    return (
        <div className={styles.content}>
            <form onSubmit={correction}>
                <table className={styles.table}>
                    <tbody className={styles.tableBody}>
                        <tr>
                            <th scope="row">名前</th>
                            <td></td>
                            <td>{user.name}</td>
                            <td></td>
                            <td></td>
                            <td colSpan={2}></td>
                        </tr>
                        <tr>
                            <th scope="row">日付</th>
                            <td></td>
                            <td>
                                {yearMonth ? formatWithDayjs({
                                    day: yearMonth,
                                    format: "YYYY年",
                                }) : null}
                            </td>
                            <td></td>
                            <td>
                                {yearMonth ? formatWithDayjs({
                                    day: yearMonth,
                                    format: "M月D日",
                                }) : null}
                            </td>
                            <td colSpan={2}></td>
                        </tr>
                        <tr>
                            <th scope="row">出勤・勤怠</th>
                            <td></td>
                            <td>
                                <input
                                    type="time"
                                    value={attendanceStartTime}
                                    onChange={(e: React.ChangeEvent<HTMLInputElement>) => setAttendanceStartTime(e.target.value)}
                                />
                            </td>
                            <td>〜</td>
                            <td>
                                <input
                                    type="time"
                                    value={attendanceEndTime}
                                    onChange={(e: React.ChangeEvent<HTMLInputElement>) => setAttendanceEndTime(e.target.value)}
                                />
                            </td>
                            <td colSpan={2}></td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div>
    )
}

export default AttendanceShowClient