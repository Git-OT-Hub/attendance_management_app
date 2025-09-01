'use client';
import styles from "@/app/(admin)/(private)/admin/attendance/list/AdminAttendanceListPage.module.scss";
import { apiClient } from "@/lib/axios/axios";
import { useEffect } from "react";

const AdminAttendanceListPage = () => {
    useEffect(() => {
        apiClient.get('/api/admin/user')
            .then((res) => {
                console.log(res);
            })
            .catch(e => {
                console.log(e);
            });
    }, []);

    return (
        <div>AdminAttendanceListPage</div>
    )
}

export default AdminAttendanceListPage