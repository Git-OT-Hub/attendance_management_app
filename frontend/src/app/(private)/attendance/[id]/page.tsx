import type { Metadata } from "next";
import type { AttendanceShowPageProps } from "@/types/attendance/attendance";

export const metadata: Metadata = {
    title: "勤怠詳細",
    description: "勤怠詳細画面を表示します。",
};

const AttendanceShowPage = async ({params}: AttendanceShowPageProps) => {
    const { id } = await params;
    console.log(id);

    return (
        <div>AttendanceShowPage</div>
    )
}

export default AttendanceShowPage