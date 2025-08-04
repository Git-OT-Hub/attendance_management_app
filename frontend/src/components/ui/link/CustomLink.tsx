import Link from "next/link";
import { CustomLinkProps } from "@/types/ui/customLink";
import styles from "@/components/ui/link/CustomLink.module.scss";

const CustomLink = ({href, text}: CustomLinkProps) => {
    return (
        <Link
            className={styles.link}
            href={href}
        >{text}</Link>
    )
}

export default CustomLink