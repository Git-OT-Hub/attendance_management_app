import styles from "@/app/(admin)/(auth)/AdminAuthLayout.module.scss";
import PublicHeader from "@/components/admin/layouts/publicHeader/PublicHeader";

const AdminAuthLayout = ({
    children,
}: Readonly<{
    children: React.ReactNode;
}>) => {
    return (
        <>
            <PublicHeader />
            <div className={styles.content}>
                {children}
            </div>
        </>
    )
}

export default AdminAuthLayout