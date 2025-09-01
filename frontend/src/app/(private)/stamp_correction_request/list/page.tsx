import styles from "@/app/(private)/stamp_correction_request/list/StampCorrectionRequestListPage.module.scss";
import type { Metadata } from "next";
import StampCorrectionRequestList from "@/components/stampCorrectionRequestList/StampCorrectionRequestList";

export const metadata: Metadata = {
    title: "申請一覧",
    description: "申請一覧画面を表示します。",
};

const StampCorrectionRequestListPage = () => {
    return (
        <>
            <div className={styles.content}>
                <h1>申請一覧</h1>
                <StampCorrectionRequestList />
            </div>
        </>
    )
}

export default StampCorrectionRequestListPage