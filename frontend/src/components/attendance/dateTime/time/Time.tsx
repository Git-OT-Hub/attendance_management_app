"use client";
import { useTime } from "@/lib/dateTime/time";
import styles from "@/components/attendance/dateTime/time/Time.module.scss";

const Time = () => {
    const time = useTime();

    return (
        <p className={styles.timer}>
            {time}
        </p>
    )
}

export default Time