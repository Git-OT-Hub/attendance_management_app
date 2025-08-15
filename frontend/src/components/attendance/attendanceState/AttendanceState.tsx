import type { AttendanceStateProps } from "@/types/attendance/attendance";
import styles from "@/components/attendance/attendanceState/AttendanceState.module.scss";

const AttendanceState = ({text}: AttendanceStateProps) => {
    return (
        <span className={styles.text}>{text}</span>
    )
}

export default AttendanceState