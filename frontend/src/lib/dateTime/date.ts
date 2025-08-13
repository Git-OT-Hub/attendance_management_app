export const formatDate = () => {
    const now = new Date();
    const year = now.getFullYear();
    const month = now.getMonth() + 1;
    const date = now.getDate();
    const day = now.getDay();
    const week = ["日", "月", "火", "水", "木", "金", "土"][day];

    return `${year}年${month}月${date}日(${week})`;
};

export const formatDateTime = () => {
    const now = new Date();
    const date = now.toISOString().split('T')[0];
    const time = now.toTimeString().split(' ')[0];

    return `${date} ${time}`;
};