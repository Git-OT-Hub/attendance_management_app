import PrivateHeader from "@/components/layouts/privateHeader/PrivateHeader";
import VerifiedAuthGuard from "@/components/auth/routeProtection/VerifiedAuthGuard";
import styles from "@/app/(private)/Private.module.scss";

const PrivateLayout = ({
    children,
}: Readonly<{
    children: React.ReactNode;
}>) => {
    return (
        <>
            <VerifiedAuthGuard>
                <PrivateHeader />
                <div className={styles.content}>
                    {children}
                </div>
            </VerifiedAuthGuard>
        </>
    );
};

export default PrivateLayout;