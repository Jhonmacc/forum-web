<script setup>
defineProps({
    value: String,
});
</script>

<template>
    <label class="block text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
