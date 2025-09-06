'use client';
import { useLayoutEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { apiClient } from "@/lib/axios/axios";
import { AxiosResponse } from "axios";
import { User } from "@/types/user/user";
import { HTTP_OK, HTTP_UNAUTHORIZED, HTTP_FORBIDDEN } from "@/constants/httpStatus";
import { flashStore } from "@/store/zustand/flashStore";
import Loading from "@/components/ui/loading/Loading";

const GuestGuard = ({
	children,
}: Readonly<{
	children: React.ReactNode;
}>) => {
    const [loading, setLoading] = useState<boolean>(true);
    const { createFlash } = flashStore();
    const router = useRouter();

    // ログアウト済みかどうかを検証
    useLayoutEffect(() => {
        apiClient.get('/api/admin/user')
            .then((res: AxiosResponse<User>) => {
                if (res.status !== HTTP_OK) {
                    console.error('予期しないエラー: ', res.status);
                    return;
                }

                if (res.status === HTTP_OK) {
                    createFlash({
                        type: "error",
                        message: "先にログアウトしてください"
                    });
                    router.push('/admin/attendance/list');
                }
            })
            .catch((e) => {
                if (e.status !== HTTP_UNAUTHORIZED && e.status !== HTTP_FORBIDDEN) {
                    console.error('予期しないエラー: ', e);

                    return;
                }

                if (e.status === HTTP_FORBIDDEN) {
                    createFlash({
                        type: "error",
                        message: "先にログアウトしてください"
                    });
                    router.push('/attendance');

                    return;
                }

                setLoading(false);
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

export default GuestGuard