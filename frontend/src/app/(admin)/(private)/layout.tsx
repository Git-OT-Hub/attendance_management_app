import styles from "@/app/(admin)/(private)/AdminPrivateLayout.module.scss";
import PrivateHeader from "@/components/admin/layouts/privateHeader/PrivateHeader";

const AdminPrivateLayout = ({
    children,
}: Readonly<{
    children: React.ReactNode;
}>) => {
    return (
        <>
                <PrivateHeader />
                <div className={styles.theme}>
                    <div className={styles.content}>
                        {children}
                    </div>
                </div>
        </>
    )
}

export default AdminPrivateLayout