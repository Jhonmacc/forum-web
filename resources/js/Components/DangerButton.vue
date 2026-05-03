<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button :type="type" class="inline-flex items-center justify-center gap-2 rounded-xl border border-transparent bg-red-500 px-4 py-2.5 text-sm font-extrabold text-white shadow-[0_4px_16px_rgba(239,68,68,0.24)] transition-all hover:-translate-y-0.5 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900 disabled:opacity-50 disabled:hover:translate-y-0">
        <slot />
    </button>
</template>
