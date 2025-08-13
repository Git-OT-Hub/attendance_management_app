import { formatDate } from "@/lib/dateTime/date";
import Time from "@/components/attendance/dateTime/time/Time";
import styles from "@/components/attendance/dateTime/DateTime.module.scss";

const DateTime = () => {
    const date = formatDate();

    return (
        <div>
            <p className={styles.date}>{date}</p>
            <Time />
        </div>
    )
}

export default DateTime