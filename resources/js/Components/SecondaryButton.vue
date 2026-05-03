<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button :type="type" class="inline-flex items-center justify-center gap-2 rounded-xl border-[1.5px] border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-2.5 text-sm font-bold text-gray-600 dark:text-gray-300 shadow-sm transition-colors hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-accent/30 focus:ring-offset-2 dark:focus:ring-offset-gray-900 disabled:opacity-40">
        <slot />
    </button>
</template>
