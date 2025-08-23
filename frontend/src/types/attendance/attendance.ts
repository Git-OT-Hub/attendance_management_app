export type AttendanceType = {
    id: number | null;
    user_id: number | null;
    start_date: string | null;
    start_time: string | null;
    end_time: string | null;
    total_breaking_time: string | null;
    actual_working_time: string | null;
    corrected_start_time: string | null;
    corrected_end_time: string | null;
    comment: string | null;
    is_correction_request: boolean | null;
    correction_request_date: string | null;
    is_approval: boolean | null;
    approval_date: string | null;
    state: string | null;
    created_at: string | null;
    updated_at: string | null;
};

export type AttendanceStateProps = {
    text: "勤務外" | "出勤中" | "休憩中" | "退勤済";
}

export type AttendanceButtonProps = {
    text: string;
    fn: () => void;
}

export type WorkingStateType = "勤務外" | "出勤中" | "休憩中" | "退勤済";

export type BreakingType = {
    breaking_id: number;
    state: string;
}

export type FinishBreakingType = {
    state: string;
}

export type FinishWorkType = {
    state: string;
}

export type AttendanceListType = {
    date: string;
    id: number | null;
    start_time: string | null;
    end_time: string | null;
    total_breaking_time: string | null;
    actual_working_time: string | null;
    year_month: string;
}

export type AttendanceShowPageProps = {
    params: Promise<{ id: string }>
}

export type AttendanceShowClientProps = {
    id: string;
}

export type BreakingShowType = {
    breaking_id?: number;
    breaking_start_time?: string;
    breaking_end_time?: string | null;
};

export type AttendanceShowType = {
    attendance_id: number;
    attendance_start_time: string;
    attendance_end_time: string | null;
    breakings: {
        [key: string]: BreakingShowType;
    };
};