"use server";
import FlashToasterClient from "@/components/ui/toaster/FlashToasterClient";
import { getFlash } from "@/lib/toaster/toaster";

const FlashToaster = async () => {
    const flash = await getFlash();
    if (!flash) {
        return null;
    }

    return (
        <FlashToasterClient flash={flash?.value}/>
    )
}

export default FlashToaster