import type { Metadata } from "next";
import AttendanceListClient from "@/components/admin/attendanceList/attendanceListClient/AttendanceListClient";

export const metadata: Metadata = {
    title: "勤怠一覧(管理者)",
    description: "勤怠一覧画面(管理者)を表示します。",
};

const AdminAttendanceListPage = () => {

    return (
        <>
            <AttendanceListClient />
        </>
    )
}

export default AdminAttendanceListPage