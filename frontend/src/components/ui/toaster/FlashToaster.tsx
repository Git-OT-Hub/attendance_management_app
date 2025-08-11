"use client";
import { flashStore } from "@/store/zustand/flashStore";
import styles from "@/components/ui/toaster/FlashToaster.module.scss";

const FlashToaster = () => {
    const { flash } = flashStore();

    if (!flash.type || !flash.message) {
        return null;
    }

    return (
        <div
            key={`${flash.type}-${flash.message}`}
            className={`${styles.toaster} ${flash.type === 'success' ? styles.success : ''} ${flash.type === 'error' ? styles.error : ''}`}
        >
            <p>{flash.message}</p>
        </div>
    )
}

export default FlashToaster