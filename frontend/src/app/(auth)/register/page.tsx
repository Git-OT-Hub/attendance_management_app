import type { Metadata } from "next";
import RegisterForm from "@/components/auth/register/RegisterForm";
import CustomLink from "@/components/ui/link/CustomLink";

export const metadata: Metadata = {
    title: "会員登録",
    description: "会員登録画面を表示します。",
};

const RegisterPage = () => {
    return (
        <>
            <h1>会員登録</h1>
            <RegisterForm />
            <CustomLink
                href="/login"
                text="ログインはこちら"
            />
        </>
    )
}

export default RegisterPage