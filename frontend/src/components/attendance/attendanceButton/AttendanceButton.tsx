import type { AttendanceButtonProps } from "@/types/attendance/attendance";
import styles from "@/components/attendance/attendanceButton/AttendanceButton.module.scss";

const AttendanceButton = ({text, fn}: AttendanceButtonProps) => {
    if (text === "出勤" || text === "退勤") {
        return (
            <button
                className={styles.blackBtn}
                onClick={fn}
            >
                {text}
            </button>
        )
    }

    if (text === "休憩入" || text === "休憩戻") {
        return (
            <button
                className={styles.whiteBtn}
                onClick={fn}
            >
                {text}
            </button>
        )
    }
}

export default AttendanceButton