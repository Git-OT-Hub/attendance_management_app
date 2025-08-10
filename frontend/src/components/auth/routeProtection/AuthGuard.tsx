'use client';
import { useLayoutEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { apiClient } from "@/lib/axios/axios";
import { AxiosResponse } from "axios";
import { User } from "@/types/user/user";
import { HTTP_OK, HTTP_UNAUTHORIZED } from "@/constants/httpStatus";
import { flashStore } from "@/store/zustand/flashStore";

const AuthGuard = ({
	children,
}: Readonly<{
	children: React.ReactNode;
}>) => {
    const [loading, setLoading] = useState<boolean>(true);
    const { addType, addMessage } = flashStore();
    const router = useRouter();

    useLayoutEffect(() => {
        apiClient.get('/api/user').then((res: AxiosResponse<User>) => {
            if (res.status !== HTTP_OK) {
                console.error('予期しないエラー: ', res.status);
                return;
            }

            setLoading(false);
        }).catch((e) => {
            if (e.status === HTTP_UNAUTHORIZED) {
                addType("error");
                addMessage("ログインが必要です");
                router.push('/login');

                return;
            }

            console.error('予期しないエラー: ', e);
        });
    }, [router]);

    return (
        <>
            {loading ? (
                <p>Loading...</p>
            ) : (
                children
            )}
        </>
    )
}

export default AuthGuard