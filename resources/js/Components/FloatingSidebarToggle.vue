<template>
    <button
        type="button"
        class="fixed z-50 flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 shadow-xl transition-all duration-300 hover:-translate-y-0.5 hover:border-accent hover:text-accent dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
        :style="{ left: open ? openLeft : closedLeft, top }"
        :title="open ? $t('profile.collapse_sidebar') : $t('profile.expand_sidebar')"
        :aria-label="open ? $t('profile.collapse_sidebar') : $t('profile.expand_sidebar')"
        @click="$emit('update:open', !open)"
    >
        <i :class="open ? 'fa-solid fa-chevron-left' : 'fa-solid fa-bars'"></i>
    </button>
</template>

<script setup>
defineProps({
    open: {
        type: Boolean,
        required: true,
    },
    openLeft: {
        type: String,
        default: '228px',
    },
    closedLeft: {
        type: String,
        default: '16px',
    },
    top: {
        type: String,
        default: '170px',
    },
});

defineEmits(['update:open']);
</script>
