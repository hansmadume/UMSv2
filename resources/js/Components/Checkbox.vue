<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:checked']);

const props = defineProps({
    checked: {
        type: [Array, Boolean],
        required: true,
    },
    value: {
        default: null,
    },
});

const proxyChecked = computed({
    get() {
        return props.checked;
    },
    set(val) {
        emit('update:checked', val);
    },
});

const handleChange = (event) => {
    emit('update:checked', event.target.checked);
};
</script>

<template>
    <label class="checkbox-label">
        <input
            type="checkbox"
            class="mui-checkbox"
            :value="value"
            :checked="proxyChecked"
            @change="handleChange"
        />
        <span class="checkbox-custom"></span>
        <slot />
    </label>
</template>