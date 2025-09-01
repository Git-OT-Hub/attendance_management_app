'use client';
import styles from "@/components/stampCorrectionRequestList/StampCorrectionRequestList.module.scss";
import { useState } from "react";
import WaitingForApprovalList from "@/components/stampCorrectionRequestList/waitingForApprovalList/WaitingForApprovalList";
import ApprovedList from "@/components/stampCorrectionRequestList/approvedList/ApprovedList";

const StampCorrectionRequestList = () => {
    const [isSelected, setIsSelected] = useState<boolean>(true);

    const listChange = (value: boolean) => {
        setIsSelected(value);
    };

    return (
        <div className={styles.content}>
            <div className={styles.header}>
                <div
                    className={`${isSelected ? styles.selected : ""}`}
                    onClick={() => listChange(true)}
                >
                    <span>承認待ち</span>
                </div>
                <div
                    className={`${isSelected ? "" : styles.selected}`}
                    onClick={() => listChange(false)}
                >
                    <span>承認済み</span>
                </div>
            </div>

            {isSelected ? (
                <WaitingForApprovalList />
            ) : (
                <ApprovedList />
            )}
        </div>
    )
}

export default StampCorrectionRequestList