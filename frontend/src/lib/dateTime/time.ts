import { useEffect, useState } from "react";
import dayjs from "dayjs";
import utc from "dayjs/plugin/utc";
import timezone from "dayjs/plugin/timezone";
import "dayjs/locale/ja";

dayjs.extend(utc);
dayjs.extend(timezone);
dayjs.tz.setDefault("Asia/Tokyo");
dayjs.locale("ja");

export const useTime = () => {
	const [time, setTime] = useState(dayjs().tz().format("HH:mm"));

	const calcTime = () => {
		setTime(dayjs().tz().format("HH:mm"));
	};

	useEffect(() => {
		const intervalId = setInterval(calcTime, 1000);

		return () => clearInterval(intervalId);
	}, []);

	return time;
};