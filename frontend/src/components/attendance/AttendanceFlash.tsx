'use client';
import { useEffect } from "react";
import { useSearchParams } from "next/navigation";
import { flashStore } from "@/store/zustand/flashStore";

const AttendanceFlash = () => {
    const searchParams = useSearchParams();
    const { createFlash } = flashStore();

    useEffect(() => {
        if (String(searchParams) === "verified=1") {
            createFlash({
                type: "success",
                message: "メール認証が完了しました。"
            });
        }
    }, [searchParams]);

    return (
        <></>
    )
}

export default AttendanceFlash