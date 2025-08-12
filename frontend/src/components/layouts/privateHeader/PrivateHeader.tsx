import Link from "next/link";
import Image from "next/image";
import Logout from "@/components/layouts/privateHeader/logout/Logout";
import styles from "@/components/layouts/privateHeader/PrivateHeader.module.scss";

const PrivateHeader = () => {
    return (
        <header className={styles.header}>
            <div className={styles.header__image}>
                <Link href="/attendance" className={styles.link}>
                    <Image
                        alt="public header logo"
                        src="/images/headerLogo/header_logo.svg"
                        fill
                        priority
                        style={{ objectFit: "contain" }}
                    />
                </Link>
            </div>
            <div className={styles.header__nav}>
                <Link
                    href="/attendance"
                    className={styles.nav__link}
                >勤怠</Link>
                <Link
                    href=""
                    className={styles.nav__link}
                >勤怠一覧</Link>
                <Link
                    href=""
                    className={styles.nav__link}
                >申請</Link>
                <Logout />
            </div>
        </header>
    );
};

export default PrivateHeader;