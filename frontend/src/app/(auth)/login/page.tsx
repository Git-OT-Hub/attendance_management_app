import type { Metadata } from "next";
import LoginForm from "@/components/auth/login/LoginForm";
import CustomLink from "@/components/ui/link/CustomLink";
import GuestGuard from "@/components/auth/routeProtection/GuestGuard";

export const metadata: Metadata = {
    title: "ログイン",
    description: "ログイン画面を表示します。",
};

const LoginPage = () => {
    return (
        <>
            <GuestGuard>
                <h1>ログイン</h1>
                <LoginForm />
                <CustomLink
                    href="/register"
                    text="会員登録はこちら"
                />
            </GuestGuard>
        </>
    )
}

export default LoginPage