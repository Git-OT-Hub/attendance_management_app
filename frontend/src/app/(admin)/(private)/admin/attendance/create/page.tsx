import styles from "@/app/(admin)/(private)/admin/attendance/create/AttendanceCreatePage.module.scss";
import type { Metadata } from "next";
import AttendanceCreateClient from "@/components/admin/attendanceCreate/attendanceCreateClient/AttendanceCreateClient";

export const metadata: Metadata = {
    title: "勤怠詳細",
    description: "勤怠詳細画面を表示します。",
};

const AttendanceCreatePage = () => {

    return (
        <>
            <div className={styles.content}>
                <h1>勤怠詳細</h1>
                <AttendanceCreateClient />
            </div>
        </>
    )
}

export default AttendanceCreatePage