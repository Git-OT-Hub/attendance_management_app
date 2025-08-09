'use client';
import { useState } from "react";
import { useRouter } from "next/navigation";
import styles from "@/components/auth/register/RegisterForm.module.scss";
import TextInput from "@/components/ui/input/TextInput";
import FormButton from "@/components/ui/button/FormButton";
import ValidationErrors from "@/components/ui/errors/ValidationErrors";
import { apiClient } from "@/lib/axios/axios";
import { AxiosResponse } from "axios";
import { ValidationErrorsType } from "@/types/errors/errors";
import { HTTP_CREATED, HTTP_UNPROCESSABLE_ENTITY } from "@/constants/httpStatus";

const RegisterForm = () => {
    const [name, setName] = useState<string>('');
    const [email, setEmail] = useState<string>('');
    const [password, setPassword] = useState<string>('');
    const [passwordConfirmed, setPasswordConfirmed] = useState<string>('');
    const [errors, setErrors] = useState<ValidationErrorsType>({
        errors: {}
    });
    console.log(errors);

    const router = useRouter();

    // laravelにユーザー登録処理のリクエストを投げる
    const register = (e: React.FormEvent<HTMLFormElement>): void => {
        e.preventDefault();

        const data = {
            name: name,
            email: email,
            password: password,
            password_confirmation: passwordConfirmed,
        };

        try {
            // CSRF保護を初期化
            apiClient.get('/sanctum/csrf-cookie').then(() => {
                // ユーザー登録処理
                apiClient.post('/api/register', data).then((res: AxiosResponse<string>) => {
                    if (res.status !== HTTP_CREATED) {
                        console.error('予期しないエラー: ', res);
                        return;
                    }

                    router.push('/email_verify');
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
            onSubmit={register}
            className={styles.form}
        >
            <div>
                <TextInput
                    label="名前"
                    type="text"
                    name="name"
                    value={name}
                    fn={(e: React.ChangeEvent<HTMLInputElement>) => setName(e.target.value)}
                />
                <ValidationErrors
                    errorKey="name"
                    errors={errors}
                />
            </div>
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
            <div>
                <TextInput
                    label="パスワード確認"
                    type="password"
                    name="password_confirmation"
                    value={passwordConfirmed}
                    fn={(e: React.ChangeEvent<HTMLInputElement>) => setPasswordConfirmed(e.target.value)}
                />
            </div>
            <div className={styles.btn}>
                <FormButton
                    text="登録する"
                />
            </div>
        </form>
    )
}

export default RegisterForm