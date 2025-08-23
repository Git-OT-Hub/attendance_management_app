import { create } from "zustand";
import type { UserStoreType } from "@/types/store/zustand/store";

export const userStore = create<UserStoreType>((set) => ({
    user: {
        id: null,
        name: null,
    },
    setUser: (value) => set((state) => ({ user: value })),
}));