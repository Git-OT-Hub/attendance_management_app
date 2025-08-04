'use client';
import { useLayoutEffect } from "react";
import { useRouter } from "next/navigation";
import { apiClient } from "@/lib/axios/axios";
import { AxiosResponse } from "axios";
import { HTTP_NO_CONTENT } from "@/constants/httpStatus";
import { deleteCookies } from "@/lib/cookies/deleteCookies";

const EmailVerifyPage = () => {
    const router = useRouter();

    // 下記をミドルウェア内で処理できないか検証する
    useLayoutEffect(() => {
        apiClient.get('/api/user').then((res: AxiosResponse<string>) => {
            console.log('ログインユーザー情報: ', res);
        }).catch((e) => {
            console.error('ログインユーザー情報取得に失敗: ', e);
        });
    }, []);

    const logout = () => {
        if (confirm("ログアウトしますか？")) {
            apiClient.post('/api/logout').then((res: AxiosResponse<string>) => {
                if (res.status !== HTTP_NO_CONTENT) {
                    console.error('予期しないエラー: ', res);
                    return;
                }

                deleteCookies();
                router.push('/');
            }).catch((e) => {
                console.error('予期しないエラー: ', e);
            });
        }
    };

    return (
        <button onClick={logout}>ログアウト</button>
    )
}

export default EmailVerifyPage