import Link from "next/link";
import Image from "next/image";
import Logout from "@/components/admin/layouts/privateHeader/logout/Logout";
import styles from "@/components/admin/layouts/privateHeader/PrivateHeader.module.scss";

const PrivateHeader = () => {
    return (
        <header className={styles.header}>
            <div className={styles.header__image}>
                <Link href="/admin/attendance/list" className={styles.link}>
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
                    href="/admin/attendance/list"
                    className={styles.nav__link}
                >勤怠一覧</Link>
                <Link
                    href="/admin/staff/list"
                    className={styles.nav__link}
                >スタッフ一覧</Link>
                <Link
                    href="/admin/stamp_correction_request/list"
                    className={styles.nav__link}
                >申請一覧</Link>
                <Logout />
            </div>
        </header>
    );
};

export default PrivateHeader;