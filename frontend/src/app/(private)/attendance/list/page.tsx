import styles from "@/app/(private)/attendance/list/AttendanceListPage.module.scss";
import type { Metadata } from "next";
import AttendanceListClient from "@/components/attendanceList/attendanceListClient/AttendanceListClient";

export const metadata: Metadata = {
    title: "勤怠一覧",
    description: "勤怠一覧画面を表示します。",
};

const AttendanceListPage = () => {
    return (
        <>
            <div className={styles.content}>
                <h1>勤怠一覧</h1>
                <AttendanceListClient />
            </div>
        </>
    )
}

export default AttendanceListPage