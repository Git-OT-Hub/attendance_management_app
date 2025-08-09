"use server";
import { cookies } from "next/headers";
import type { FlashToasterType } from "@/types/flash/flash";

export const setFlash = async (flash: FlashToasterType) => {
    const cookieStore = await cookies();
    cookieStore.set("flash", JSON.stringify(flash), {
        path: "/",
    });
};

export const getFlash = async () => {
    const cookieStore = await cookies();
    return cookieStore.get("flash");
};

export const deleteFlash = async () => {
    const cookieStore = await cookies();
    cookieStore.delete("flash");
};