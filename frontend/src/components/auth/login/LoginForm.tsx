'use client';
import { useState } from "react";
import { useRouter } from "next/navigation";
import styles from "@/components/auth/login/LoginForm.module.scss";
import TextInput from "@/components/ui/input/TextInput";
import FormButton from "@/components/ui/button/FormButton";
import ValidationErrors from "@/components/ui/errors/ValidationErrors";
import { apiClient } from "@/lib/axios/axios";
import { AxiosResponse } from "axios";
import { setFlash } from "@/lib/toaster/toaster";
import { ValidationErrorsType } from "@/types/errors/errors";
import { HTTP_OK, HTTP_UNPROCESSABLE_ENTITY } from "@/constants/httpStatus";

const LoginForm = () => {
    const [email, setEmail] = useState<string>('');
    const [password, setPassword] = useState<string>('');
    const [errors, setErrors] = useState<ValidationErrorsType>({
        errors: {}
    });

    const router = useRouter();

    // laravelにログイン処理のリクエストを投げる
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
                apiClient.post('/api/login', data).then((res: AxiosResponse<string>) => {
                    if (res.status !== HTTP_OK) {
                        console.error('予期しないエラー: ', res);
                        return;
                    }

                    setFlash({
                        type: "success",
                        message: "ログインしました",
                    }).then(() => {
                        router.push('/attendance');
                    });
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
                    text="ログインする"
                />
            </div>
        </form>
    )
}

export default LoginForm