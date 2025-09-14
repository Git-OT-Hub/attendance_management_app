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

export type AttendanceCorrectionShowPageProps = {
    params: Promise<{ id: string }>
}

export type AttendanceShowClientProps = {
    id: string;
}

export type AttendanceCorrectionShowClientProps = {
    id: string;
}

export type BreakingShowType = {
    breaking_id?: number;
    breaking_start_time?: string;
    breaking_end_time?: string | null;
};

export type BreakingCorrectionShowType = {
    start_time?: string;
    end_time?: string;
};

export type AttendanceShowType = {
    user_name: string;
    attendance_id: number;
    attendance_start_date: string;
    attendance_start_time: string;
    attendance_end_time: string | null;
    attendance_correction_request_date: string | null;
    comment?: string;
    breakings: {
        [key: string]: BreakingShowType;
    };
};

export type AttendanceCorrectionShowType = {
    user_name: string;
    start_date: string;
    start_time: string;
    end_time: string;
    comment: string;
    breakings: {
        [key: string]: BreakingCorrectionShowType;
    };
};

export type correctionDataUseState = {
    user_name: string;
    start_date: string;
    start_time: string;
    end_time: string;
    comment: string;
};

export type AttendanceShowUseState = {
    attendance_id: number;
    attendance_start_date: string;
    attendance_start_time: string;
    attendance_end_time: string | null;
    attendance_correction_request_date: string | null;
}

export type AttendanceShowNameAndDate = {
    user_name: string;
    attendance_start_date: string;
}

export type WaitingForApprovalListType = {
    id: number;
    user_name: string;
    start_date: string;
    comment: string;
    correction_request_date: string;
}

export type ApprovedListType = {
    id: number;
    user_name: string;
    start_date: string;
    comment: string;
    correction_request_date: string;
}

export type AdminAttendanceListType = {
    id: number | null;
    start_time: string | null;
    end_time: string | null;
    total_breaking_time: string | null;
    actual_working_time: string | null;
    user_name: string;
    user_id: number;
}

export type AdminAttendanceShowUseState = {
    user_id: number;
    attendance_start_date: string;
    attendance_start_time: string;
    attendance_end_time: string;
}