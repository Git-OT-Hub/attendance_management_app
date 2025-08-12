"use client";
import { useRouter } from "next/navigation";
import { RiArrowGoBackLine } from "react-icons/ri";
import styles from "@/style/not-found/NotFound.module.scss";

const NotFound = () => {
    const router = useRouter();

    const handleBack = () => {
        router.back();
    };

    return (
        <div
            className={styles.content}
        >
            <p>お探しのページが見つかりませんでした</p>
            <RiArrowGoBackLine
                className={styles.back}
                onClick={handleBack}
            />
        </div>
    )
}

export default NotFound