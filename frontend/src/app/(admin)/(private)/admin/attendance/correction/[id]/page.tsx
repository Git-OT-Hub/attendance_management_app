import styles from "@/app/(admin)/(private)/admin/attendance/correction/[id]/AttendanceCorrectionShowPage.module.scss";
import type { AttendanceCorrectionShowPageProps } from "@/types/attendance/attendance";
import type { Metadata } from "next";
import AttendanceCorrectionShowClient from "@/components/admin/attendanceCorrectionShow/AttendanceCorrectionShowClient/AttendanceCorrectionShowClient";

export const metadata: Metadata = {
    title: "勤怠修正履歴の詳細(管理者)",
    description: "勤怠修正履歴の詳細画面(管理者)を表示します。",
};

const AttendanceCorrectionShowPage = async ({params}: AttendanceCorrectionShowPageProps) => {
    const { id } = await params;

    return (
        <>
            <div className={styles.content}>
                <h1>勤怠詳細</h1>
                <AttendanceCorrectionShowClient id={id}/>
            </div>
        </>
    )
}

export default AttendanceCorrectionShowPage