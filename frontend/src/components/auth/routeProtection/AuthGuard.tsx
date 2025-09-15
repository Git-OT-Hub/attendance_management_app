'use client';
import { useLayoutEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { apiClient } from "@/lib/axios/axios";
import { AxiosResponse } from "axios";
import { User } from "@/types/user/user";
import { HTTP_OK, HTTP_UNAUTHORIZED, HTTP_FORBIDDEN } from "@/constants/httpStatus";
import { flashStore } from "@/store/zustand/flashStore";
import Loading from "@/components/ui/loading/Loading";

const AuthGuard = ({
	children,
}: Readonly<{
	children: React.ReactNode;
}>) => {
    const [loading, setLoading] = useState<boolean>(true);
    const { createFlash } = flashStore();
    const router = useRouter();

    // ログイン済み、かつ、メール認証が未完了かどうかを検証
    useLayoutEffect(() => {
        apiClient.get('/api/user').then((res: AxiosResponse<User>) => {
            if (res.status !== HTTP_OK) {
                console.error('予期しないエラー: ', res.status);
                return;
            }

            if (res.data.email_verified_at) {
                createFlash({
                    type: "error",
                    message: "メール認証は既に完了しています"
                });
                router.push('/attendance');

                return;
            }

            setLoading(false);
        }).catch((e) => {
            if (e.status === HTTP_UNAUTHORIZED) {
                createFlash({
                    type: "error",
                    message: "ログインが必要です"
                });
                router.push('/login');

                return;
            }

            if (e.status === HTTP_FORBIDDEN) {
                createFlash({
                    type: "error",
                    message: "一般ユーザー以外は許可されていません"
                });
                router.push('/admin/attendance/list');

                return;
            }

            console.error('予期しないエラー: ', e);
        });
    }, [router]);

    return (
        <>
            {loading ? (
                <Loading />
            ) : (
                children
            )}
        </>
    )
}

export default AuthGuard