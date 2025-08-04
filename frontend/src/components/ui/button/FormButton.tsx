import styles from "@/components/ui/button/FormButton.module.scss";
import { FormButtonProps } from "@/types/ui/button";

const FormButton = ({text}: FormButtonProps) => {
    return (
        <button
            type="submit"
            className={styles.btn}
        >{text}</button>
    )
}

export default FormButton