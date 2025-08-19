import dayjs from "dayjs";
import utc from "dayjs/plugin/utc";
import timezone from "dayjs/plugin/timezone";
import "dayjs/locale/ja";

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