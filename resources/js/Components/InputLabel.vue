<script setup>
defineProps({
    value: {
        type: String,
    },
    for: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <label class="mui-label" :for="for">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
