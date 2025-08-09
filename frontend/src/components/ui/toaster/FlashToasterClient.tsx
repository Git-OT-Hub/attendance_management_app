"use client";
import { useEffect, useState } from "react";
import type { FlashToasterType, FlashToasterClientProps } from "@/types/flash/flash";
import styles from "@/components/ui/toaster/FlashToasterClient.module.scss";
import { deleteFlash } from "@/lib/toaster/toaster";

const FlashToasterClient = ({ flash }: FlashToasterClientProps) => {
    const [toaster, setToaster] = useState<FlashToasterType>({
        type: "",
        message: "",
    });

    useEffect(() => {
        if (!!flash) {
            const data: FlashToasterType = JSON.parse(flash);
            setToaster({
                type: data.type,
                message: data.message,
            });

            const deleteFlashCookies = async () => {
                await deleteFlash();
            };
            deleteFlashCookies();
        }
    }, [flash]);

    return (
        <div className={`${styles.toaster} ${toaster.type === 'success' ? styles.success : ''} ${toaster.type === 'error' ? styles.error : ''}`}>
            <p>{toaster.message}</p>
        </div>
    )
}

export default FlashToasterClient