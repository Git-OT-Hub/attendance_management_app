"use server";
import { cookies } from "next/headers";
import type { FlashValueType } from "@/types/flash/flash";

// 通常のフラッシュ
export const setFlash = async (flash: FlashValueType) => {
    const cookieStore = await cookies();
    cookieStore.set("flash", JSON.stringify(flash), {
        path: "/",
    });
};

// ルート保護用のフラッシュ
export const setFlashForGuard = async (flash: FlashValueType) => {
    const cookieStore = await cookies();
    cookieStore.set("flash_guard", JSON.stringify(flash), {
        path: "/",
    });
};

export const getFlash = async () => {
    const cookieStore = await cookies();
    const flashCookiesArray = cookieStore.getAll().filter((cookie) => {
        return cookie.name === "flash" || cookie.name === "flash_guard";
    });
    const flashCookies = Object.fromEntries(
        flashCookiesArray.map((cookie) => [cookie.name, cookie])
    );

    if (flashCookies.flash?.value && !flashCookies.flash_guard?.value) {
        return flashCookies.flash;
    } else if (!flashCookies.flash?.value && flashCookies.flash_guard?.value) {
        return flashCookies.flash_guard;
    } else {
        return null;
    };
};

export const deleteFlash = async (name: string) => {
    const cookieStore = await cookies();
    cookieStore.delete(name);
    console.log('delete count!!!');
};