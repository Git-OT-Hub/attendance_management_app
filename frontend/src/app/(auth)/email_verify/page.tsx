import type { Metadata } from "next";
import ResendEmail from "@/components/auth/email_verify/ResendEmail";
import styles from "@/app/(auth)/email_verify/EmailVerifyPage.module.scss";

export const metadata: Metadata = {
    title: "メール認証誘導",
    description: "メール認証誘導画面を表示します。",
};

const EmailVerifyPage = () => {
    return (
        <div className={styles.content}>
            <p>登録していただいたメールアドレスに認証メールを送付しました。</p>
            <p>メール認証を完了してください。</p>
            <div className={styles.authentication}>
                <a href="http://localhost:8025">
                    認証はこちらから
                </a>
            </div>
            <ResendEmail />
        </div>
    )
}

export default EmailVerifyPage