'use client';
import styles from "@/components/admin/attendanceCreate/attendanceCreateClient/AttendanceCreateClient.module.scss";
import { useSearchParams, useRouter } from "next/navigation";
import { useState, useEffect } from "react";
import { flashStore } from "@/store/zustand/flashStore";
import type { BreakingShowType, AdminAttendanceShowUseState } from "@/types/attendance/attendance";
import type { ValidationErrorsType } from "@/types/errors/errors";
import { formatWithDayjs, formatDateTime } from "@/lib/dateTime/date";
import ValidationErrors from "@/components/ui/errors/ValidationErrors";
import FormButton from "@/components/ui/button/FormButton";
import { apiClient } from "@/lib/axios/axios";
import { AxiosResponse } from "axios";
import { HTTP_UNPROCESSABLE_ENTITY, HTTP_CREATED } from "@/constants/httpStatus";

const AttendanceCreateClient = () => {
    const searchParams = useSearchParams();
    const yearMonth = searchParams.get("yearMonth");
    const userId = Number(searchParams.get("userId"));
    const userName = searchParams.get("userName");
    const { createFlash } = flashStore();
    const router = useRouter();

    const [attendance, setAttendance] = useState<AdminAttendanceShowUseState>({
        user_id: 0,
        attendance_start_date: '',
        attendance_start_time: '',
        attendance_end_time: '',
    });
    const [breaking, setBreaking] = useState<BreakingShowType>({
        breaking_start_time: '',
        breaking_end_time: '',
    });
    const [comment, setComment] = useState<string>('');
    const [errors, setErrors] = useState<ValidationErrorsType>({
        errors: {}
    });

    useEffect(() => {
        if (yearMonth && userId) {
            setAttendance((prev) => ({
                ...prev,
                user_id: userId,
                attendance_start_date: formatWithDayjs({
                    day: yearMonth,
                    format: "YYYY-MM-DD",
                })
            }))
        }
    }, [yearMonth, userId]);

    if (!yearMonth || !userId || !userName) {
        createFlash({
            type: "error",
            message: "詳細ボタンから移動してください"
        });
        router.push('/admin/attendance/list');

        return;
    }

    const correction = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();

        const data = {
            attendance,
            breaking,
            comment,
            correction_request_date: formatDateTime()
        };

        if (confirm("この内容で修正しますか？\nこの操作は、取り消しできませんがよろしいですか？")) {
            apiClient.post('/api/admin/attendance/create', data)
                .then((res: AxiosResponse<number>) => {
                    setErrors({errors: {}});
                    if (res.status !== HTTP_CREATED) {
                        console.error('予期しないエラー: ', res.status);
                        return;
                    }

                    createFlash({
                        type: "success",
                        message: "修正しました"
                    });
                    router.push(`/admin/attendance/${res.data}`);
                })
                .catch((e) => {
                    // バリデーションエラー表示
                    if (e.response.status === HTTP_UNPROCESSABLE_ENTITY && e.response.data.errors) {
                        setErrors({errors: {...e.response.data.errors}});

                        return;
                    }

                    console.error('予期しないエラー: ', e);
                    createFlash({
                        type: "error",
                        message: "エラーが発生しました。"
                    });
                    router.push('/admin/attendance/list');
                });
        }
    };

    return (
        <div className={styles.content}>
            <form onSubmit={correction}>
                <table className={styles.table}>
                    <tbody className={styles.tableBody}>
                        <tr>
                            <th scope="row">名前</th>
                            <td>{userName}</td>
                            <td></td>
                            <td></td>
                            <td colSpan={2}></td>
                        </tr>
                        <tr>
                            <th scope="row">日付</th>
                            <td>
                                {formatWithDayjs({
                                    day: yearMonth,
                                    format: "YYYY年",
                                })}
                            </td>
                            <td></td>
                            <td>
                                {formatWithDayjs({
                                    day: yearMonth,
                                    format: "M月D日",
                                })}
                            </td>
                            <td colSpan={2}></td>
                        </tr>
                        <tr>
                            <th scope="row">出勤・勤怠</th>
                            <td>
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
                            </td>
                            <td>〜</td>
                            <td>
                                <input
                                    type="datetime-local"
                                    value={attendance.attendance_end_time}
                                    onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                                        setAttendance((prev) => ({
                                            ...prev,
                                            attendance_end_time: e.target.value
                                        }))
                                    }
                                />
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
                        <tr>
                            <th scope="row">休憩</th>
                            <td>
                                <input
                                    type="datetime-local"
                                    value={breaking.breaking_start_time || ""}
                                    onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                                        setBreaking((prev) => ({
                                            ...prev,
                                            breaking_start_time: e.target.value
                                        }))
                                    }
                                />
                            </td>
                            <td>〜</td>
                            <td>
                                <input
                                    type="datetime-local"
                                    value={breaking.breaking_end_time || ""}
                                    onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                                        setBreaking((prev) => ({
                                            ...prev,
                                            breaking_end_time: e.target.value
                                        }))
                                    }
                                />
                            </td>
                            <td colSpan={2}>
                                <ValidationErrors
                                    errorKey="breaking.breaking_start_time"
                                    errors={errors}
                                />
                                <ValidationErrors
                                    errorKey="breaking.breaking_end_time"
                                    errors={errors}
                                />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">備考</th>
                            <td colSpan={3} className={styles.comment}>
                                <textarea
                                    rows={3}
                                    value={comment}
                                    onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => setComment(e.target.value)}
                                />
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
                    <div className={styles.btn}>
                        <FormButton
                            text="修正"
                        />
                    </div>
                </div>
            </form>
        </div>
    )
}

export default AttendanceCreateClient