import PublicHeader from "@/components/layouts/publicHeader/PublicHeader";
import styles from "@/app/(private)/Private.module.scss";

const PrivateLayout = ({
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

export default PrivateLayout;