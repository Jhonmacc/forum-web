<template>
    <section class="bg-white px-5 pb-7 pt-2 dark:bg-gray-900 sm:px-7">
        <div class="post-content prose prose-lg max-w-none dark:prose-invert prose-headings:font-extrabold prose-a:text-accent" v-html="content"></div>
    </section>
</template>

<script setup>
defineProps({
    content: {
        type: String,
        default: '',
    },
});
</script>

<style scoped>
.post-content :deep(img) {
    border-radius: 0.75rem;
}

.post-content :deep(p) {
    line-height: 1.8;
}

.post-content :deep(.post-reference-card),
.post-content :deep(.link-preview-card) {
    display: flex;
    gap: 1rem;
    margin: 1rem 0;
    padding: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 1rem;
    background: #f9fafb;
    color: #111827;
    text-decoration: none;
}

.dark .post-content :deep(.post-reference-card),
.dark .post-content :deep(.link-preview-card) {
    border-color: #374151;
    background: #111827;
    color: #f9fafb;
}

.post-content :deep(.post-reference-title),
.post-content :deep(.link-preview-title) {
    display: block;
    font-weight: 900;
}

.post-content :deep(.post-reference-meta),
.post-content :deep(.link-preview-domain),
.post-content :deep(.link-preview-description) {
    display: block;
    margin-top: 0.2rem;
    color: #6b7280;
    font-size: 0.9rem;
}

.dark .post-content :deep(.post-reference-meta),
.dark .post-content :deep(.link-preview-domain),
.dark .post-content :deep(.link-preview-description) {
    color: #9ca3af;
}

.post-content :deep(.link-preview-image) {
    width: 120px;
    height: 86px;
    flex-shrink: 0;
    border-radius: 0.85rem;
    object-fit: cover;
}
</style>
