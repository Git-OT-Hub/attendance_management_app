export type AttendanceType = {
    id: number | null;
    user_id: number | null;
    start_time: string | null;
    end_time: string | null;
    total_breaking_time: string | null;
    total_working_time: string | null;
    corrected_start_time: string | null;
    corrected_end_time: string | null;
    comment: string | null;
    is_correction_request: boolean | null;
    correction_request_date: string | null;
    is_approval: boolean | null;
    approval_date: string | null;
    status: string | null;
    created_at: string | null;
    updated_at: string | null;
};