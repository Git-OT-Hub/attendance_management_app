export type FlashValueType = {
    type: "success" | "error" | "";
    message: string;
};

export type FlashStoreType = {
    flash: FlashValueType;
    createFlash: (value: FlashValueType) => void;
}

export type UserValueType = {
    id: number | null;
    name: string | null;
};

export type UserStoreType = {
    user: UserValueType;
    setUser: (value: UserValueType) => void;
}