<template>
    <section class="bg-white dark:bg-gray-900">
        <div class="p-5 sm:p-7">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0 flex-1">
                    <button @click="$emit('open-user', post.user?.id)" class="inline-flex items-center gap-3 text-left group">
                        <img :src="post.user?.profile_photo_url" :alt="post.user?.name" class="w-10 h-10 rounded-full object-cover border border-gray-200 dark:border-gray-700">
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-extrabold text-gray-900 dark:text-white group-hover:text-accent">{{ post.user?.name }}</span>
                            <span class="block text-xs text-gray-400 dark:text-gray-500">{{ relativeDate }}</span>
                        </span>
                    </button>

                    <h1 class="mt-4 text-2xl sm:text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white leading-tight">
                        {{ post.title }}
                    </h1>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span
                            v-for="tag in post.tags || []"
                            :key="tag.id"
                            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-black uppercase tracking-wider"
                            :style="{ backgroundColor: withAlpha(tag.color || '#F5B800', 0.16), color: tag.color || '#F5B800' }"
                        >
                            <i :class="tag.icon || 'fa-solid fa-tag'"></i>
                            {{ tag.name }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        @click="$emit('vote')"
                        class="h-12 min-w-16 rounded-2xl border px-4 flex items-center justify-center gap-2 font-extrabold transition-colors"
                        :class="post.liked || post.liked_by_current_user
                            ? 'border-accent bg-accent/15 text-accent'
                            : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-300 hover:text-accent hover:border-accent/40'"
                        :title="$t('profile.upvote')"
                    >
                        <i class="fa-solid fa-arrow-up"></i>
                        {{ post.likes_count || 0 }}
                    </button>
                    <div class="h-12 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-4 flex items-center gap-2 text-sm font-bold text-gray-500 dark:text-gray-300">
                        <i class="fa-solid fa-comments"></i>
                        {{ post.comments_count || commentsCount }}
                    </div>
                    <button
                        v-if="canManage"
                        @click="$emit('edit')"
                        class="w-12 h-12 rounded-2xl border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-300 hover:text-accent hover:border-accent/40 hover:bg-accent/10"
                        :title="$t('common.edit')"
                    >
                        <i class="fa-solid fa-pen"></i>
                    </button>
                    <button
                        v-if="canManage"
                        @click="$emit('delete')"
                        class="w-12 h-12 rounded-2xl border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-300 hover:text-red-500 hover:border-red-300 hover:bg-red-50 dark:hover:bg-red-950/30"
                        :title="$t('common.delete')"
                    >
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
defineProps({
    post: {
        type: Object,
        required: true,
    },
    relativeDate: {
        type: String,
        required: true,
    },
    canManage: {
        type: Boolean,
        default: false,
    },
    commentsCount: {
        type: Number,
        default: 0,
    },
});

defineEmits(['vote', 'edit', 'delete', 'open-user']);

const withAlpha = (hex, alpha) => {
    const clean = String(hex || '#F5B800').replace('#', '');
    if (clean.length !== 6) return `rgba(245, 184, 0, ${alpha})`;

    const r = parseInt(clean.slice(0, 2), 16);
    const g = parseInt(clean.slice(2, 4), 16);
    const b = parseInt(clean.slice(4, 6), 16);

    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
};
</script>
