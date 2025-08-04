import { InputProps } from "@/types/ui/input";
import styles from "@/components/ui/input/TextInput.module.scss";

const TextInput = ({label, type, name, value, fn}: InputProps) => {
    return (
        <div className={styles.input}>
            <label>
                <span>{label}</span>
                <input
                    type={type}
                    name={name}
                    value={value}
                    onChange={fn}
                />
            </label>
        </div>
    )
}

export default TextInput