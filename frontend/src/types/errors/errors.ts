export type ValidationErrorsType = {
    errors: Record<string, string[]>;
}

export type ValidationErrorsProps = {
    errorKey: string;
    errors: ValidationErrorsType;
}