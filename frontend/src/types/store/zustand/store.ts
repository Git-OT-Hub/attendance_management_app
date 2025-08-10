export type FlashStoreType = {
    type: string;
    message: string;
    addType: (type: string) => void;
    addMessage: (message: string) => void;
}