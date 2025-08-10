import { create } from "zustand";
import { FlashStoreType } from "@/types/store/zustand/store";

export const flashStore = create<FlashStoreType>((set) => ({
    type: "",
    message: "",
    addType: (type) => set((state) => ({ type: type })),
    addMessage: (message) => set((state) => ({ message: message })),
}));