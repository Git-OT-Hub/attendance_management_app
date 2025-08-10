// "use server";
"use client";
import FlashToasterClient from "@/components/ui/toaster/FlashToasterClient";
import { getFlash } from "@/lib/toaster/toaster";
import { useEffect } from "react";
import { flashStore } from "@/store/zustand/flashStore";

// const FlashToaster = async () => {
const FlashToaster = () => {
    // const flash = await getFlash();
    // console.log("flash-toaster: ", flash);
    // if (!flash) {
    //     return null;
    // }
    const { type, message } = flashStore();

    return (
        <>
        {/* <FlashToasterClient
            flashName={flash?.name}
            flashValue={flash?.value}
        /> */}
            <p>{type}</p>
            <p>{message}</p>
        </>
    )
}

export default FlashToaster