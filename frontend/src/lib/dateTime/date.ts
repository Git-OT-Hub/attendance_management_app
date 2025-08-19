import dayjs from "dayjs";
import utc from "dayjs/plugin/utc";
import timezone from "dayjs/plugin/timezone";
import "dayjs/locale/ja";
import type { FormatWithDayjsType } from "@/types/lib/dayjs/dayjs";

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

// subtractも引数を指定できるようにする
export const subtractWithDayjs = ({ day, format }: FormatWithDayjsType) => {
    return dayjs(day).tz().subtract(1, "month").format(format);
};