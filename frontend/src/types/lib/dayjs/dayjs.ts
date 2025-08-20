import type { ManipulateType } from "dayjs";

export type FormatWithDayjsType = {
    day?: string;
    format?: string;
};

export type AdjustmentWithDayjsType = {
    day: string;
    num: number;
    unit: ManipulateType;
    format: string;
};