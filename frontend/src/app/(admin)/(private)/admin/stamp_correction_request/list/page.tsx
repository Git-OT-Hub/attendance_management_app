import styles from "@/app/(admin)/(private)/admin/stamp_correction_request/list/StampCorrectionRequestListPage.module.scss";
import type { Metadata } from "next";
import StampCorrectionRequestList from "@/components/admin/stampCorrectionRequestList/StampCorrectionRequestList";

export const metadata: Metadata = {
    title: "申請一覧(管理者)",
    description: "申請一覧画面(管理者)を表示します。",
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