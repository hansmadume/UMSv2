<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="mui-btn mui-btn-outlined"
    >
        <slot />
    </button>
</template>
