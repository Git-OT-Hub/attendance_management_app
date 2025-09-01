import type { Metadata } from "next";
import AdminLoginForm from "@/components/admin/auth/login/AdminLoginForm";

export const metadata: Metadata = {
    title: "管理者ログイン",
    description: "管理者ログイン画面を表示します。",
};

const AdminLoginPage = () => {
    return (
        <>

                <h1>管理者ログイン</h1>
                <AdminLoginForm />

        </>
    )
}

export default AdminLoginPage