export type FlashValueType = {
    type: "success" | "error" | "";
    message: string;
};

export type FlashStoreType = {
    flash: FlashValueType;
    createFlash: (value: FlashValueType) => void;
}

export type UserStoreType = {
    loginUserId: number | null;
    setUserId: (id: number) => void;
}