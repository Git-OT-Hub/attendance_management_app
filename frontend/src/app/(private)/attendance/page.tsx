import AttendanceFlash from "@/components/attendance/attendanceFlash/AttendanceFlash";
import DateTime from "@/components/attendance/dateTime/DateTime";
import AttendanceClient from "@/components/attendance/attendanceClient/AttendanceClient";
import styles from "@/app/(private)/attendance/AttendancePage.module.scss";
import type { Metadata } from "next";

export const metadata: Metadata = {
    title: "勤怠登録",
    description: "勤怠登録画面を表示します。",
};

const AttendancePage = () => {
    return (
        <>
            <AttendanceFlash />
            <div className={styles.content}>
                <AttendanceClient>
                    <DateTime />
                </AttendanceClient>
            </div>
        </>
    )
}

export default AttendancePage