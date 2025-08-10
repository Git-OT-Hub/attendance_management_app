"use client";
import { useEffect, useState } from "react";
import type { FlashValueType, FlashToasterClientProps } from "@/types/flash/flash";
import styles from "@/components/ui/toaster/FlashToasterClient.module.scss";
import { deleteFlash } from "@/lib/toaster/toaster";

const FlashToasterClient = ({ flashName, flashValue }: FlashToasterClientProps) => {
    const [toaster, setToaster] = useState<FlashValueType>({
        type: "",
        message: "",
    });

    useEffect(() => {
        if (!!flashValue && !!flashName) {
            const parsedFlashValue: FlashValueType = JSON.parse(flashValue);

            setToaster({
                type: parsedFlashValue.type,
                message: parsedFlashValue.message,
            });

            const deleteFlashCookies = async (name: string) => {
                await deleteFlash(name);
            };
            deleteFlashCookies(flashName);

            console.log('effect count');
        }
    }, [flashValue, flashName]);

    return (
        <div className={`${styles.toaster} ${toaster.type === 'success' ? styles.success : ''} ${toaster.type === 'error' ? styles.error : ''}`}>
            <p>{toaster.message}</p>
        </div>
    )
}

export default FlashToasterClient