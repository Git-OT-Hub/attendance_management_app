'use client';
import { useState } from "react";
import { useRouter } from "next/navigation";
import styles from "@/components/admin/auth/login/AdminLoginForm.module.scss";
import TextInput from "@/components/ui/input/TextInput";
import FormButton from "@/components/ui/button/FormButton";
import ValidationErrors from "@/components/ui/errors/ValidationErrors";
import { apiClient } from "@/lib/axios/axios";
import { AxiosResponse } from "axios";
import { ValidationErrorsType } from "@/types/errors/errors";
import { HTTP_OK, HTTP_UNPROCESSABLE_ENTITY } from "@/constants/httpStatus";
import { flashStore } from "@/store/zustand/flashStore";

const AdminLoginForm = () => {
    const [email, setEmail] = useState<string>('');
    const [password, setPassword] = useState<string>('');
    const [errors, setErrors] = useState<ValidationErrorsType>({
        errors: {}
    });
    const { createFlash } = flashStore();
    const router = useRouter();

    const login = (e: React.FormEvent<HTMLFormElement>): void => {
        e.preventDefault();

        const data = {
            email: email,
            password: password,
        };

        try {
            // CSRF保護を初期化
            apiClient.get('/sanctum/csrf-cookie').then(() => {
                // ログイン処理
                apiClient.post('/api/admin/login', data).then((res: AxiosResponse<string>) => {
                    if (res.status !== HTTP_OK) {
                        console.error('予期しないエラー: ', res);
                        return;
                    }

                    console.log(res);

                    createFlash({
                        type: "success",
                        message: "ログインしました"
                    });
                    router.push('/admin/attendance/list');
                }).catch((e) => {
                    // バリデーションエラー表示
                    if (e.response.status === HTTP_UNPROCESSABLE_ENTITY && e.response.data.errors) {
                        setErrors({errors: {...e.response.data.errors}});
                        return;
                    }

                    console.error('予期しないエラー: ', e);
                });
            }).catch((e) => {
                console.error('CSRF Cookie取得に失敗しました: ', e);
            });
        } catch (error) {
            console.error('予期しないエラー: ', error);
        }
    };

    return (
        <form
            onSubmit={login}
            className={styles.form}
        >
            <div>
                <TextInput
                    label="メールアドレス"
                    type="email"
                    name="email"
                    value={email}
                    fn={(e: React.ChangeEvent<HTMLInputElement>) => setEmail(e.target.value)}
                />
                <ValidationErrors
                    errorKey="email"
                    errors={errors}
                />
            </div>
            <div>
                <TextInput
                    label="パスワード"
                    type="password"
                    name="password"
                    value={password}
                    fn={(e: React.ChangeEvent<HTMLInputElement>) => setPassword(e.target.value)}
                />
                <ValidationErrors
                    errorKey="password"
                    errors={errors}
                />
            </div>
            <div className={styles.btn}>
                <FormButton
                    text="管理者ログインする"
                />
            </div>
        </form>
    )
}

export default AdminLoginForm