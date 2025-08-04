import PublicHeader from "@/components/layouts/publicHeader/PublicHeader";
import styles from "@/app/(auth)/Auth.module.scss";

const AuthLayout = ({
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
    );
};

export default AuthLayout;