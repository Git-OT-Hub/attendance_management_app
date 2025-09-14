import styles from "@/app/(private)/attendance/correction/[id]/AttendanceCorrectionShowPage.module.scss";
import type { AttendanceCorrectionShowPageProps } from "@/types/attendance/attendance";
import type { Metadata } from "next";
import AttendanceCorrectionShowClient from "@/components/attendanceCorrectionShow/AttendanceCorrectionShowClient/AttendanceCorrectionShowClient";

export const metadata: Metadata = {
    title: "勤怠修正履歴の詳細",
    description: "勤怠修正履歴の詳細画面を表示します。",
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