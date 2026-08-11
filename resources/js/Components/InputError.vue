<script setup>
defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
    <div v-show="message" style="color: var(--danger); font-size: 0.875rem; margin-top: 4px;">
        {{ message }}
    </div>
</template>
