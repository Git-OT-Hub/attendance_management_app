'use client';
import { useLayoutEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { apiClient } from "@/lib/axios/axios";
import { AxiosResponse } from "axios";
import { User } from "@/types/user/user";
import { HTTP_OK, HTTP_UNAUTHORIZED, HTTP_FORBIDDEN } from "@/constants/httpStatus";
import { flashStore } from "@/store/zustand/flashStore";
import Loading from "@/components/ui/loading/Loading";

const VerifiedAuthGuard = ({
	children,
}: Readonly<{
	children: React.ReactNode;
}>) => {
    const [loading, setLoading] = useState<boolean>(true);
    const { createFlash } = flashStore();
    const router = useRouter();

    // ログイン済み、かつ、メール認証済みかどうかを検証
    useLayoutEffect(() => {
        apiClient.get('/api/admin/user')
            .then((res: AxiosResponse<User>) => {
                if (res.status !== HTTP_OK) {
                    console.error('予期しないエラー: ', res.status);
                    return;
                }

                if (!res.data.email_verified_at) {
                    createFlash({
                        type: "error",
                        message: "メール認証を完了する必要があります"
                    });

                    return;
                }

                setLoading(false);
            })
            .catch((e) => {
                if (e.status === HTTP_UNAUTHORIZED) {
                    createFlash({
                        type: "error",
                        message: "ログインが必要です"
                    });
                    router.push('/admin/login');

                    return;
                }

                if (e.status === HTTP_FORBIDDEN) {
                    createFlash({
                        type: "error",
                        message: "管理者以外は許可されていません"
                    });
                    router.push('/attendance');

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

export default VerifiedAuthGuard