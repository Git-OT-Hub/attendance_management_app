import type { Metadata } from "next";
import type { AttendanceShowPageProps } from "@/types/attendance/attendance";
import styles from "@/app/(private)/attendance/[id]/AttendanceShowPage.module.scss";
import AttendanceShowClient from "@/components/attendanceShow/attendanceShowClient/AttendanceShowClient";

export const metadata: Metadata = {
    title: "勤怠詳細",
    description: "勤怠詳細画面を表示します。",
};

const AttendanceShowPage = async ({params}: AttendanceShowPageProps) => {
    const { id } = await params;

    return (
        <>
            <div className={styles.content}>
                <h1>勤怠詳細</h1>
                <AttendanceShowClient id={id}/>
            </div>
        </>
    )
}

export default AttendanceShowPage