export type FlashValueType = {
    type: "success" | "error" | "";
    message: string;
};

export type FlashStoreType = {
    flash: FlashValueType;
    createFlash: (value: FlashValueType) => void;
}