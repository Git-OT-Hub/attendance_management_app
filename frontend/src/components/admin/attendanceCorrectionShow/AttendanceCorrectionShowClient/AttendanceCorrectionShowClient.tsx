"use client";
import styles from "@/components/admin/attendanceCorrectionShow/AttendanceCorrectionShowClient/AttendanceCorrectionShowClient.module.scss";
import type { AttendanceCorrectionShowClientProps } from "@/types/attendance/attendance";
import { useLayoutEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { apiClient } from "@/lib/axios/axios";
import { AxiosResponse } from "axios";
import { HTTP_OK } from "@/constants/httpStatus";
import { formatWithDayjs } from "@/lib/dateTime/date";
import { flashStore } from "@/store/zustand/flashStore";
import Loading from "@/components/ui/loading/Loading";
import type { AttendanceCorrectionShowType, correctionDataUseState, BreakingCorrectionShowType } from "@/types/attendance/attendance";

const AttendanceCorrectionShowClient = ({id}: AttendanceCorrectionShowClientProps) => {
    const [loading, setLoading] = useState<boolean>(true);
    const { createFlash } = flashStore();
    const router = useRouter();

    const [correctionData, setCorrectionData] = useState<correctionDataUseState>({
        user_name: '',
        start_date: '',
        start_time: '',
        end_time: '',
        comment: '',
    });
    const [breakings, setBreakings] = useState<{ [key: string]: BreakingCorrectionShowType }>({});

    useLayoutEffect(() => {
        apiClient.get(`/api/admin/attendance/correction/${id}`)
            .then((res: AxiosResponse<AttendanceCorrectionShowType>) => {
                if (res.status !== HTTP_OK) {
                    console.error('予期しないエラー: ', res.status);
                    return;
                }

                setCorrectionData({
                    user_name: res.data.user_name,
                    start_date: res.data.start_date,
                    start_time: res.data.start_time,
                    end_time: res.data.end_time,
                    comment: res.data.comment,
                });
                setBreakings(res.data.breakings);

                setLoading(false);
            })
            .catch((e) => {
                console.error('予期しないエラー: ', e);
                createFlash({
                    type: "error",
                    message: "エラーが発生しました。"
                });
                router.push('/admin/stamp_correction_request/list');
            });
    }, [id]);

    if (loading) {
        return (
            <Loading />
        )
    }

    return (
        <div className={styles.content}>
            <table className={styles.table}>
                <tbody className={styles.tableBody}>
                    <tr>
                        <th scope="row">名前</th>
                        <td>{correctionData.user_name}</td>
                        <td></td>
                        <td></td>
                        <td colSpan={2}></td>
                    </tr>
                    <tr>
                        <th scope="row">日付</th>
                        <td>
                            {formatWithDayjs({
                                day: correctionData.start_date,
                                format: "YYYY年",
                            })}
                        </td>
                        <td></td>
                        <td>
                            {formatWithDayjs({
                                day: correctionData.start_date,
                                format: "M月D日",
                            })}
                        </td>
                        <td colSpan={2}></td>
                    </tr>
                    <tr>
                        <th scope="row">出勤・勤怠</th>
                        <td>
                            <span>
                                {correctionData.start_time ? formatWithDayjs({
                                    day: correctionData.start_time,
                                    format: 'YYYY/MM/DD HH:mm',
                                }) : ''}
                            </span>
                        </td>
                        <td>〜</td>
                        <td>
                            <span>
                                {correctionData.end_time ? formatWithDayjs({
                                    day: correctionData.end_time,
                                    format: 'YYYY/MM/DD HH:mm',
                                }) : ''}
                            </span>
                        </td>
                        <td colSpan={2}></td>
                    </tr>
                    {Object.entries(breakings).map(([label, breaking]) => (
                        <tr key={label}>
                            <th scope="row">{label}</th>
                            <td>
                                <span>
                                    {breaking.start_time ? formatWithDayjs({
                                        day: breaking.start_time,
                                        format: 'YYYY/MM/DD HH:mm',
                                    }) : ''}
                                </span>
                            </td>
                            <td>〜</td>
                            <td>
                                <span>
                                    {breaking.end_time ? formatWithDayjs({
                                        day: breaking.end_time,
                                        format: 'YYYY/MM/DD HH:mm',
                                    }) : ''}
                                </span>
                            </td>
                            <td colSpan={2}></td>
                        </tr>
                    ))}
                    <tr>
                        <th scope="row">備考</th>
                        <td colSpan={3} className={styles.comment}>
                                <span>{correctionData.comment ? correctionData.comment : ''}</span>
                        </td>
                        <td colSpan={2}></td>
                    </tr>
                </tbody>
            </table>
            <div className={styles.btnArea}>
                <span className={styles.btnAreaMessage}>
                    承認済み
                </span>
            </div>
        </div>
    )
}

export default AttendanceCorrectionShowClient