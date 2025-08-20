import dayjs from "dayjs";
import utc from "dayjs/plugin/utc";
import timezone from "dayjs/plugin/timezone";
import "dayjs/locale/ja";
import type { FormatWithDayjsType, AdjustmentWithDayjsType } from "@/types/lib/dayjs/dayjs";

dayjs.extend(utc);
dayjs.extend(timezone);
dayjs.tz.setDefault("Asia/Tokyo");
dayjs.locale("ja");

export const formatDate = () => {
    return dayjs().tz().format("YYYY年M月D日(ddd)");
};

export const formatDateTime = () => {
    return dayjs().tz().second(0).format("YYYY-MM-DD HH:mm:ss");
};

export const formatWithDayjs = ({ day, format }: FormatWithDayjsType) => {
    return dayjs(day).tz().format(format);
};

export const subtractWithDayjs = ({ day, num, unit, format }: AdjustmentWithDayjsType) => {
    return dayjs(day).tz().subtract(num, unit).format(format);
};

export const addWithDayjs = ({ day, num, unit, format }: AdjustmentWithDayjsType) => {
    return dayjs(day).tz().add(num, unit).format(format);
};