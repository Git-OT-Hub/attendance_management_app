import styles from "@/app/(private)/attendance/list/AttendanceListPage.module.scss";
import AttendanceListClient from "@/components/attendanceList/attendanceListClient/AttendanceListClient";

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