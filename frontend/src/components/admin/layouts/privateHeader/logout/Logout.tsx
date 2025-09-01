"use client";
import { useRouter } from "next/navigation";
import { apiClient } from "@/lib/axios/axios";
import { AxiosResponse } from "axios";
import { HTTP_NO_CONTENT } from "@/constants/httpStatus";
import { deleteCookies } from "@/lib/cookies/deleteCookies";
import styles from "@/components/admin/layouts/privateHeader/logout/Logout.module.scss";
import { flashStore } from "@/store/zustand/flashStore";

const Logout = () => {
    const { createFlash } = flashStore();
    const router = useRouter();

    const logout = () => {
        if (confirm("ログアウトしますか？")) {
            apiClient.post('/api/admin/logout').then((res: AxiosResponse<string>) => {
                if (res.status !== HTTP_NO_CONTENT) {
                    console.error('予期しないエラー: ', res);
                    return;
                }

                createFlash({
                    type: "success",
                    message: "ログアウトしました"
                });

                deleteCookies().then(() => {
                    router.push('/admin/login');
                });
            }).catch((e) => {
                createFlash({
                    type: "error",
                    message: "ログアウトに失敗しました"
                });
                console.error('予期しないエラー: ', e);
            });
        }
    }

    return (
        <span
            onClick={logout}
            className={styles.text}
        >
            ログアウト
        </span>
    )
}

export default Logout