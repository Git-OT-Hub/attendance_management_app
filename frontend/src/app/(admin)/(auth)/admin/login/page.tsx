import type { Metadata } from "next";
import AdminLoginForm from "@/components/admin/auth/login/AdminLoginForm";
import GuestGuard from "@/components/admin/auth/routeProtection/GuestGuard";

export const metadata: Metadata = {
    title: "管理者ログイン",
    description: "管理者ログイン画面を表示します。",
};

const AdminLoginPage = () => {
    return (
        <>
            <GuestGuard>
                <h1>管理者ログイン</h1>
                <AdminLoginForm />
            </GuestGuard>
        </>
    )
}

export default AdminLoginPage