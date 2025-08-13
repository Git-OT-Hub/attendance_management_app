import { useEffect, useState } from "react";

export const useTime = () => {
	const [time, setTime] = useState(new Date());

	const calcTime = () => {
		setTime(new Date());
	};

	useEffect(() => {
		const intervalId = setInterval(calcTime, 1000);

		return () => clearInterval(intervalId);
	}, []);

	return time;
};