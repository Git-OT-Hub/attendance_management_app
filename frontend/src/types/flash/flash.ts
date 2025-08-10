export type FlashValueType = {
    type: "success" | "error" | "";
    message: string;
};

export type FlashToasterClientProps = {
    flashName: string | undefined;
    flashValue: string | undefined;
};