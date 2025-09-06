import styles from "@/app/(admin)/(private)/AdminPrivateLayout.module.scss";
import VerifiedAuthGuard from "@/components/admin/auth/routeProtection/VerifiedAuthGuard";
import PrivateHeader from "@/components/admin/layouts/privateHeader/PrivateHeader";

const AdminPrivateLayout = ({
    children,
}: Readonly<{
    children: React.ReactNode;
}>) => {
    return (
        <>
            <VerifiedAuthGuard>
                <PrivateHeader />
                <div className={styles.theme}>
                    <div className={styles.content}>
                        {children}
                    </div>
                </div>
            </VerifiedAuthGuard>
        </>
    )
}

export default AdminPrivateLayout