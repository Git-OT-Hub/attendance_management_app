export type InputProps = {
    label: string;
    type: string;
    name: string;
    value: string;
    fn: (e: React.ChangeEvent<HTMLInputElement>) => void;
}