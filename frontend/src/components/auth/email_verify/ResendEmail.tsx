"use client";
import styles from "@/components/auth/email_verify/ResendEmail.module.scss";
import { apiClient } from "@/lib/axios/axios";
import { HTTP_ACCEPTED, HTTP_NO_CONTENT } from "@/constants/httpStatus";
import { flashStore } from "@/store/zustand/flashStore";

const ResendEmail = () => {
    const { createFlash } = flashStore();

    const resendAuthenticationEmail = () => {
        if (confirm("認証メールを再送しますか？")) {
            apiClient.post('/email/verification-notification').then((res) => {
                if (res.status === HTTP_ACCEPTED) {
                    createFlash({
                        type: "success",
                        message: "登録していただいたメールアドレスに認証メールを送付しました。"
                    });
                } else if (res.status === HTTP_NO_CONTENT) {
                    createFlash({
                        type: "error",
                        message: "既にメール認証が完了しているため、認証メールは送付できません。"
                    });
                } else {
                    console.error('予期しないエラー: ', res);
                }
            }).catch(() => {
                createFlash({
                    type: "error",
                    message: "認証メールの再送に失敗しました。"
                });
            });
        }
    };

    return (
        <div className={styles.resendEmail}>
            <span
                onClick={resendAuthenticationEmail}
            >認証メールを再送する</span>
        </div>
    )
}

export default ResendEmail