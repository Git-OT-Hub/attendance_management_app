import { create } from "zustand";
import type { UserStoreType } from "@/types/store/zustand/store";

export const userStore = create<UserStoreType>((set) => ({
    loginUserId: null,
    setUserId: (id) => set((state) => ({ loginUserId: id })),
}));