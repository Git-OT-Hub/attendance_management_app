'use server'
import { cookies } from "next/headers";

export const deleteCookies = async () => {
    const cookieStore = await cookies();
    cookieStore.delete('attendance_management_app_session');
    cookieStore.delete('XSRF-TOKEN');
};