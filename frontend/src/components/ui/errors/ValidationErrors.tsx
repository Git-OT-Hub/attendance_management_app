import styles from "@/components/ui/errors/ValidationErrors.module.scss";
import { ValidationErrorsProps } from "@/types/errors/errors";

const ValidationErrors = ({errorKey, errors}: ValidationErrorsProps) => {
    const errorMessages = errors.errors[errorKey] || [];

    return (
        <div className={styles.errors}>
            <ul>
                {errorMessages && errorMessages.map((error, idx) => {
                    return (
                        <li key={idx}>{error}</li>
                    );
                })}
            </ul>
        </div>
    )
}

export default ValidationErrors