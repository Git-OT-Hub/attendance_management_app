"use client";
import styles from "@/components/attendanceShow/attendanceShowClient/AttendanceShowClient.module.scss";
import type { AttendanceShowClientProps } from "@/types/attendance/attendance";
import { useLayoutEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { apiClient } from "@/lib/axios/axios";
import { AxiosResponse } from "axios";
import type { AttendanceShowType, BreakingShowType, AttendanceShowUseState, AttendanceShowNameAndDate } from "@/types/attendance/attendance";
import type { ValidationErrorsType } from "@/types/errors/errors";
import { HTTP_OK, HTTP_FORBIDDEN, HTTP_UNPROCESSABLE_ENTITY } from "@/constants/httpStatus";
import { formatWithDayjs, formatDateTime } from "@/lib/dateTime/date";
import { flashStore } from "@/store/zustand/flashStore";
import Loading from "@/components/ui/loading/Loading";
import FormButton from "@/components/ui/button/FormButton";
import ValidationErrors from "@/components/ui/errors/ValidationErrors";

const AttendanceShowClient = ({id}: AttendanceShowClientProps) => {
    const [loading, setLoading] = useState<boolean>(true);
    const { createFlash } = flashStore();
    const router = useRouter();

    const [nameAndDate, setNameAndDate] = useState<AttendanceShowNameAndDate>({
        user_name: '',
        attendance_start_date: '',
    });
    const [attendance, setAttendance] = useState<AttendanceShowUseState>({
        attendance_id: 0,
        attendance_start_date: '',
        attendance_start_time: '',
        attendance_end_time: '',
        attendance_correction_request_date: '',
    });
    const [breakings, setBreakings] = useState<{ [key: string]: BreakingShowType }>({});
    const [comment, setComment] = useState<string>('');
    const [errors, setErrors] = useState<ValidationErrorsType>({
        errors: {}
    });

    useLayoutEffect(() => {
        apiClient.get(`/api/attendance/${id}`)
            .then((res: AxiosResponse<AttendanceShowType>) => {
                if (res.status !== HTTP_OK) {
                    console.error('予期しないエラー: ', res.status);
                    return;
                }

                console.log(res);

                setNameAndDate({
                    user_name: res.data.user_name,
                    attendance_start_date: res.data.attendance_start_date,
                });
                setAttendance({
                    attendance_id: res.data.attendance_id,
                    attendance_start_date: res.data.attendance_start_date,
                    attendance_start_time: res.data.attendance_start_time,
                    attendance_end_time: res.data.attendance_end_time,
                    attendance_correction_request_date: res.data.attendance_correction_request_date,
                });
                setBreakings(res.data.breakings);
                if (res.data.comment) {
                    setComment(res.data.comment);
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

    const handleBreakingChange = (
        key: string,
        field: "breaking_start_time" | "breaking_end_time",
        value: string
    ) => {
        setBreakings((prev) => ({
            ...prev,
            [key]: {
                ...prev[key],
                [field]: value
            }
        }));
    };

    const correction = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();

        const data = {
            attendance,
            breakings,
            comment,
            correction_request_date: formatDateTime()
        };
        console.log(data);

        if (confirm("この内容で修正申請しますか？\nこの操作は、取り消しできませんがよろしいですか？")) {
            apiClient.patch('/api/attendance/correction', data)
                .then((res: AxiosResponse<AttendanceShowType>) => {
                    setErrors({errors: {}});
                    if (res.status !== HTTP_OK) {
                        console.error('予期しないエラー: ', res.status);
                        return;
                    }

                    console.log(res);

                    setNameAndDate({
                        user_name: res.data.user_name,
                        attendance_start_date: res.data.attendance_start_date,
                    });
                    setAttendance({
                        attendance_id: res.data.attendance_id,
                        attendance_start_date: res.data.attendance_start_date,
                        attendance_start_time: res.data.attendance_start_time,
                        attendance_end_time: res.data.attendance_end_time,
                        attendance_correction_request_date: res.data.attendance_correction_request_date,
                    });
                    setBreakings(res.data.breakings);
                    if (res.data.comment) {
                        setComment(res.data.comment);
                    }
                    setLoading(false);

                    createFlash({
                        type: "success",
                        message: "修正依頼しました"
                    });
                })
                .catch((e) => {
                    // バリデーションエラー表示
                    if (e.response.status === HTTP_UNPROCESSABLE_ENTITY && e.response.data.errors) {
                        setErrors({errors: {...e.response.data.errors}});

                        return;
                    }

                    console.error('予期しないエラー: ', e);
                });
        }
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
                            <td>{nameAndDate.user_name}</td>
                            <td></td>
                            <td></td>
                            <td colSpan={2}></td>
                        </tr>
                        <tr>
                            <th scope="row">日付</th>
                            <td>
                                {formatWithDayjs({
                                    day: nameAndDate.attendance_start_date,
                                    format: "YYYY年",
                                })}
                            </td>
                            <td></td>
                            <td>
                                {formatWithDayjs({
                                    day: nameAndDate.attendance_start_date,
                                    format: "M月D日",
                                })}
                            </td>
                            <td colSpan={2}></td>
                        </tr>
                        <tr>
                            <th scope="row">出勤・勤怠</th>
                            <td>
                                {attendance.attendance_correction_request_date ? (
                                    <span>
                                        {attendance.attendance_start_time ? formatWithDayjs({
                                            day: attendance.attendance_start_time,
                                            format: 'YYYY/MM/DD HH:mm',
                                        }) : ''}
                                    </span>
                                ) : (
                                    <input
                                        type="datetime-local"
                                        value={attendance.attendance_start_time}
                                        onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                                            setAttendance((prev) => ({
                                                ...prev,
                                                attendance_start_time: e.target.value
                                            }))
                                        }
                                    />
                                )}
                            </td>
                            <td>〜</td>
                            <td>
                                {attendance.attendance_correction_request_date ? (
                                    <span>
                                        {attendance.attendance_end_time ? formatWithDayjs({
                                            day: attendance.attendance_end_time,
                                            format: 'YYYY/MM/DD HH:mm',
                                        }) : ''}
                                    </span>
                                ) : (
                                    <input
                                        type="datetime-local"
                                        value={attendance.attendance_end_time || ""}
                                        onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                                            setAttendance((prev) => ({
                                                ...prev,
                                                attendance_end_time: e.target.value
                                            }))
                                        }
                                    />
                                )}
                            </td>
                            <td colSpan={2}>
                                <ValidationErrors
                                    errorKey="attendance.attendance_start_time"
                                    errors={errors}
                                />
                                <ValidationErrors
                                    errorKey="attendance.attendance_end_time"
                                    errors={errors}
                                />
                            </td>
                        </tr>
                        {Object.entries(breakings).map(([label, breaking]) => (
                            <tr key={label}>
                                <th scope="row">{label}</th>
                                <td>
                                    {attendance.attendance_correction_request_date ? (
                                        <span>
                                            {breaking.breaking_start_time ? formatWithDayjs({
                                                day: breaking.breaking_start_time,
                                                format: 'YYYY/MM/DD HH:mm',
                                            }) : ''}
                                        </span>
                                    ) : (
                                        <input
                                            type="datetime-local"
                                            value={breaking.breaking_start_time || ""}
                                            onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                                                handleBreakingChange(label, "breaking_start_time", e.target.value)
                                            }
                                        />
                                    )}
                                </td>
                                <td>〜</td>
                                <td>
                                    {attendance.attendance_correction_request_date ? (
                                        <span>
                                            {breaking.breaking_end_time ? formatWithDayjs({
                                                day: breaking.breaking_end_time,
                                                format: 'YYYY/MM/DD HH:mm',
                                            }) : ''}
                                        </span>
                                    ) : (
                                        <input
                                            type="datetime-local"
                                            value={breaking.breaking_end_time || ""}
                                            onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                                                handleBreakingChange(label, "breaking_end_time", e.target.value)
                                            }
                                        />
                                    )}
                                </td>
                                <td colSpan={2}>
                                    <ValidationErrors
                                        errorKey={`breakings.${label}.breaking_start_time`}
                                        errors={errors}
                                    />
                                    <ValidationErrors
                                        errorKey={`breakings.${label}.breaking_end_time`}
                                        errors={errors}
                                    />
                                </td>
                            </tr>
                        ))}
                        <tr>
                            <th scope="row">備考</th>
                            <td colSpan={3} className={styles.comment}>
                                {attendance.attendance_correction_request_date ? (
                                    <span>{comment ? comment : ''}</span>
                                ) : (
                                    <textarea
                                        rows={3}
                                        value={comment}
                                        onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => setComment(e.target.value)}
                                    />
                                )}
                            </td>
                            <td colSpan={2}>
                                <ValidationErrors
                                    errorKey="comment"
                                    errors={errors}
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div className={styles.btnArea}>
                    {attendance.attendance_correction_request_date ? (
                        <span className={styles.btnAreaMessage}>・承認待ちのため修正はできません。</span>
                    ) : (
                        <div className={styles.btn}>
                            <FormButton
                                text="修正"
                            />
                        </div>
                    )}
                </div>
            </form>
        </div>
    )
}

export default AttendanceShowClient