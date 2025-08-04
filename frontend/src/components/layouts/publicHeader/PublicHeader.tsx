import Link from "next/link";
import Image from "next/image";
import styles from "@/components/layouts/publicHeader/PublicHeader.module.scss";

const PublicHeader = () => {
    return (
        <header className={styles.header}>
            <div className={styles.header__image}>
                <Link href="/" className={styles.link}>
                    <Image
                        alt="public header logo"
                        src="/images/headerLogo/header_logo.svg"
                        fill
                        priority
                        style={{ objectFit: "contain" }}
                    />
                </Link>
            </div>
            <div className={styles.header__blank}></div>
        </header>
    );
};

export default PublicHeader;