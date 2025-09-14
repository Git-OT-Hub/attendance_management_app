import type { Metadata } from "next";
import type { AttendanceStaffPageProps } from "@/types/attendance/attendance";
import AttendanceStaffClient from "@/components/admin/attendanceStaff/attendanceStaffClient/AttendanceStaffClient";

export const metadata: Metadata = {
    title: "スタッフ別勤怠一覧",
    description: "スタッフ別勤怠一覧画面を表示します。",
};

const AttendanceStaffPage = async ({params}: AttendanceStaffPageProps) => {
    const { id } = await params;

    return (
        <>
            <AttendanceStaffClient id={id}/>
        </>
    )
}

export default AttendanceStaffPage