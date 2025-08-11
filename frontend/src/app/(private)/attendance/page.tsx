'use client';
import AttendanceFlash from "@/components/attendance/AttendanceFlash";
import { useRouter } from "next/navigation";
import { apiClient } from "@/lib/axios/axios";
import { AxiosResponse } from "axios";
import { HTTP_NO_CONTENT } from "@/constants/httpStatus";
import { deleteCookies } from "@/lib/cookies/deleteCookies";

const AttendancePage = () => {
    const router = useRouter();

    const logout = () => {
        if (confirm("ログアウトしますか？")) {
            apiClient.post('/api/logout').then((res: AxiosResponse<string>) => {
                if (res.status !== HTTP_NO_CONTENT) {
                    console.error('予期しないエラー: ', res);
                    return;
                }

                deleteCookies().then(() => {
                    router.push('/');
                });
            }).catch((e) => {
                console.error('予期しないエラー: ', e);
            });
        }
    };

    return (
        <>
            <AttendanceFlash />
            <div>
                AttendancePage
                <button onClick={logout}>ログアウト</button>
            </div>
        </>
    )
}

export default AttendancePage