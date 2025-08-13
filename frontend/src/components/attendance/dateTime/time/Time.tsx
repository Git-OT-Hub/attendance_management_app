"use client";
import { useTime } from "@/lib/dateTime/time";
import styles from "@/components/attendance/dateTime/time/Time.module.scss";

const Time = () => {
    const time = useTime();

    return (
        <p className={styles.timer}>
            {time.toLocaleTimeString([], {
                hour: "2-digit",
                minute: "2-digit",
            })}
        </p>
    )
}

export default Time