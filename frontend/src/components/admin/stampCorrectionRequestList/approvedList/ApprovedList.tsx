import styles from "@/components/admin/stampCorrectionRequestList/approvedList/ApprovedList.module.scss";
import Link from "next/link";
import { useState, useEffect } from "react";
import type { ApprovedListType } from "@/types/attendance/attendance";
import Loading from "@/components/ui/loading/Loading";
import { apiClient } from "@/lib/axios/axios";
import { AxiosResponse } from "axios";
import { HTTP_OK } from "@/constants/httpStatus";
import { formatWithDayjs } from "@/lib/dateTime/date";

const ApprovedList = () => {
    const [loading, setLoading] = useState<boolean>(true);
    const [records, setRecords] = useState<ApprovedListType[]>([]);

    useEffect(() => {
        apiClient.get('/api/admin/attendance/correction_request_list/approved')
            .then((res: AxiosResponse<ApprovedListType[]>) => {
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
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody className={styles.tableBody}>
                    {records.map((record, idx) => (
                        <tr key={idx}>
                            <td>承認済み</td>
                            <td>{record.user_name}</td>
                            <td>
                                {formatWithDayjs({
                                    day: record.start_date,
                                    format: "YYYY/MM/DD"
                                })}
                            </td>
                            <td
                                className={styles.comment}
                            >{record.comment}</td>
                            <td>
                                {formatWithDayjs({
                                    day: record.correction_request_date,
                                    format: "YYYY/MM/DD"
                                })}
                            </td>
                            <td>
                                <Link
                                    className={styles.tableLink}
                                    href={`/admin/attendance/correction/${record.id}`}
                                >詳細</Link>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </>
    )
}

export default ApprovedList