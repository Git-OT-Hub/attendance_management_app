import PrivateHeader from "@/components/layouts/privateHeader/PrivateHeader";
import styles from "@/app/(private)/Private.module.scss";

const PrivateLayout = ({
    children,
}: Readonly<{
    children: React.ReactNode;
}>) => {
    return (
        <>
            <PrivateHeader />
            <div className={styles.content}>
                {children}
            </div>
        </>
    );
};

export default PrivateLayout;