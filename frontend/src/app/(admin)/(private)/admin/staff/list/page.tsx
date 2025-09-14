import styles from "@/app/(admin)/(private)/admin/staff/list/StaffListPage.module.scss";
import type { Metadata } from "next";
import StaffListClient from "@/components/admin/staffList/staffListClient/StaffListClient";

export const metadata: Metadata = {
    title: "スタッフ一覧",
    description: "スタッフ一覧画面を表示します。",
};

const StaffListPage = () => {
    return (
        <>
            <div className={styles.content}>
                <h1>スタッフ一覧</h1>
                <StaffListClient />
            </div>
        </>
    )
}

export default StaffListPage