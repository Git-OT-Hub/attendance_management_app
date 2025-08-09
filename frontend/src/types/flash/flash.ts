export type FlashToasterType = {
    type: "success" | "error" | "";
    message: string;
};

export type FlashToasterClientProps = {
    flash: string | undefined;
};