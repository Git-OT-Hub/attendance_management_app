import { FaSpinner } from "react-icons/fa";
import styles from "@/components/ui/loading/Loading.module.scss";

const Loading = () => {
    return (
        <div className={styles.content}>
            <FaSpinner className={styles.spinner} />
            <div className={styles.text}>Now Loading...</div>
        </div>
    )
}

export default Loading