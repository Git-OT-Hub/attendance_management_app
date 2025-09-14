'use client';
import styles from "@/components/admin/staffList/staffListClient/StaffListClient.module.scss";
import Link from "next/link";
import { useState, useEffect } from "react";
import Loading from "@/components/ui/loading/Loading";
import { apiClient } from "@/lib/axios/axios";
import { AxiosResponse } from "axios";
import { HTTP_OK } from "@/constants/httpStatus";
import type { StaffListType } from "@/types/user/user";

const StaffListClient = () => {
    const [loading, setLoading] = useState<boolean>(true);
    const [records, setRecords] = useState<StaffListType[]>([]);

    useEffect(() => {
        apiClient.get('/api/admin/staff/list')
            .then((res: AxiosResponse<StaffListType[]>) => {
                if (res.status !== HTTP_OK) {
                    console.error('予期しないエラー: ', res.status);
                    return;
                }

                setRecords(res.data);
                setLoading(false);
            })
            .catch((e) => {
                console.error('予期しないエラー: ', e);
            });
    }, []);

    if (loading) {
        return (
            <Loading />
        )
    }

    return (
        <>
            <table className={styles.table}>
                <thead className={styles.tableHead}>
                    <tr>
                        <th>名前</th>
                        <th>メールアドレス</th>
                        <th>月次勤怠</th>
                    </tr>
                </thead>
                <tbody className={styles.tableBody}>
                    {records.map((record, idx) => (
                        <tr key={idx}>
                            <td>{record.name}</td>
                            <td
                                className={styles.email}
                            >{record.email}</td>
                            <td>
                                <Link
                                    className={styles.tableLink}
                                    href={`/admin/attendance/staff/${record.id}`}
                                >詳細</Link>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </>
    )
}

export default StaffListClient