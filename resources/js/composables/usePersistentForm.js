import { watch, onMounted } from 'vue';

export function usePersistentForm(form, key, exclude = []) {
    const storageKey = `form_${key}`;

    onMounted(() => {
        const saved = localStorage.getItem(storageKey);
        if (saved) {
            try {
                const data = JSON.parse(saved);
                Object.keys(data).forEach((k) => {
                    if (!exclude.includes(k) && k in form) {
                        form[k] = data[k];
                    }
                });
            } catch (e) {
                // ignore parse errors
            }
        }
    });

    watch(
        form,
        (newForm) => {
            const toSave = { ...newForm };
            exclude.forEach((k) => delete toSave[k]);
            localStorage.setItem(storageKey, JSON.stringify(toSave));
        },
        { deep: true }
    );

    const clear = () => {
        localStorage.removeItem(storageKey);
    };

    return { clear };
}
