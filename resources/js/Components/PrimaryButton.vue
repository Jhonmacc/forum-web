<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
});
</script>

<template>
    <button :type="type" class="inline-flex items-center justify-center gap-2 rounded-xl border border-transparent bg-accent px-4 py-2.5 text-sm font-extrabold text-white shadow-[0_4px_16px_rgba(245,184,0,0.28)] transition-all hover:-translate-y-0.5 hover:bg-accent-dark focus:outline-none focus:ring-2 focus:ring-accent/40 focus:ring-offset-2 dark:focus:ring-offset-gray-900 disabled:opacity-50 disabled:hover:translate-y-0">
        <slot />
    </button>
</template>
