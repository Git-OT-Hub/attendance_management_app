import { create } from "zustand";
import { FlashStoreType } from "@/types/store/zustand/store";

export const flashStore = create<FlashStoreType>((set) => ({
    flash: {
        type: "",
        message: ""
    },
    createFlash: (value) => set((state) => ({ flash: value })),
}));