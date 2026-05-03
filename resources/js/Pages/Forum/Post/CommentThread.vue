<template>
    <section class="border-t border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="p-5">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-extrabold text-gray-900 dark:text-white">{{ $t('comments.comments') }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $t('comments.thread_subtitle') }}</p>
                </div>
                <div class="rounded-full bg-gray-100 dark:bg-gray-800 px-3 py-1 text-xs font-black text-gray-500 dark:text-gray-300">
                    {{ comments.length }} {{ comments.length === 1 ? $t('comments.comment_count_one') : $t('comments.comment_count_many') }}
                </div>
            </div>
        </div>

        <div class="px-5 pb-6 space-y-5">
            <slot name="composer"></slot>

            <div v-if="comments.length" class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <span>{{ $t('comments.sort_by') }}</span>
                    <div class="relative">
                        <button
                            type="button"
                            @click="isSortOpen = !isSortOpen"
                            class="inline-flex items-center gap-2 rounded-xl bg-gray-100 px-3 py-2 font-extrabold text-gray-700 transition hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                        >
                            <i :class="selectedSort.icon" class="w-4 text-center text-accent"></i>
                            {{ selectedSort.label }}
                            <i class="fa-solid fa-chevron-down text-[10px] text-gray-400"></i>
                        </button>

                        <div
                            v-if="isSortOpen"
                            class="absolute left-0 top-full z-30 mt-2 w-56 overflow-hidden rounded-2xl border border-gray-200 bg-white p-1 shadow-2xl dark:border-gray-700 dark:bg-gray-900"
                        >
                            <button
                                v-for="option in sortOptions"
                                :key="option.id"
                                type="button"
                                @click="selectSort(option.id)"
                                class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left text-sm font-extrabold transition hover:bg-accent/10 hover:text-accent"
                                :class="activeSort === option.id ? 'bg-accent/15 text-accent' : 'text-gray-600 dark:text-gray-200'"
                            >
                                <i :class="option.icon" class="w-5 text-center"></i>
                                {{ option.label }}
                            </button>
                        </div>
                    </div>
                </div>

                <label class="flex w-full items-center gap-2 rounded-full border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-500 transition focus-within:border-accent lg:max-w-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                    <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                    <input
                        v-model="searchQuery"
                        type="search"
                        class="w-full border-0 bg-transparent p-0 text-sm text-gray-900 placeholder-gray-400 outline-none focus:ring-0 dark:text-gray-100"
                        :placeholder="$t('comments.search_placeholder')"
                    >
                </label>
            </div>

            <div v-if="visibleComments.length" class="space-y-4">
                <CommentNode
                    v-for="comment in visibleComments"
                    :key="`${comment.kind}-${comment.id}`"
                    :node="comment"
                    :current-user-id="currentUserId"
                    :post-author-id="postAuthorId"
                    @like="$emit('like', $event)"
                    @reply="$emit('reply', $event)"
                    @edit="$emit('edit', $event)"
                    @delete="$emit('delete', $event)"
                    @open-user="$emit('open-user', $event)"
                />
            </div>

            <div v-else-if="comments.length" class="py-12 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800">
                    <i class="fa-solid fa-magnifying-glass text-xl"></i>
                </div>
                <div class="font-extrabold text-gray-900 dark:text-white">{{ $t('comments.no_filtered_comments_title') }}</div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $t('comments.no_filtered_comments') }}</p>
            </div>

            <div v-else class="py-12 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-800 text-gray-400">
                    <i class="fa-regular fa-comments text-xl"></i>
                </div>
                <div class="font-extrabold text-gray-900 dark:text-white">{{ $t('comments.no_comments_title') }}</div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $t('comments.no_comments') }}</p>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import CommentNode from './CommentNode.vue';

const props = defineProps({
    comments: {
        type: Array,
        default: () => [],
    },
    currentUserId: {
        type: Number,
        default: null,
    },
    postAuthorId: {
        type: Number,
        default: null,
    },
});

defineEmits(['like', 'reply', 'edit', 'delete', 'open-user']);

const { t } = useI18n();
const activeSort = ref('best');
const isSortOpen = ref(false);
const searchQuery = ref('');

const sortOptions = computed(() => [
    { id: 'best', label: t('comments.sort_best'), icon: 'fa-regular fa-star' },
    { id: 'most_voted', label: t('comments.sort_most_voted'), icon: 'fa-solid fa-arrow-up-from-bracket' },
    { id: 'newest', label: t('comments.sort_newest'), icon: 'fa-regular fa-clock' },
    { id: 'controversial', label: t('comments.sort_controversial'), icon: 'fa-solid fa-arrows-up-down' },
    { id: 'oldest', label: t('comments.sort_oldest'), icon: 'fa-regular fa-window-minimize' },
    { id: 'questions', label: t('comments.sort_questions'), icon: 'fa-solid fa-magnifying-glass' },
]);

const selectedSort = computed(() => sortOptions.value.find(option => option.id === activeSort.value) || sortOptions.value[0]);

const visibleComments = computed(() => sortTree(filterTree(props.comments)));

const selectSort = (sort) => {
    activeSort.value = sort;
    isSortOpen.value = false;
};

const filterTree = (nodes = []) => {
    const query = normalize(searchQuery.value);

    return nodes
        .map(node => {
            const children = filterTree(node.children || []);
            const matchesQuery = !query || searchableText(node).includes(query);
            const matchesQuestionMode = activeSort.value !== 'questions' || isQuestion(node) || children.length > 0;

            if ((matchesQuery || children.length > 0) && matchesQuestionMode) {
                return { ...node, children };
            }

            return null;
        })
        .filter(Boolean);
};

const sortTree = (nodes = []) => [...nodes]
    .sort(compareNodes)
    .map(node => ({
        ...node,
        children: sortTree(node.children || []),
    }));

const compareNodes = (a, b) => {
    if (activeSort.value === 'oldest') {
        return timestamp(a) - timestamp(b);
    }

    if (activeSort.value === 'newest') {
        return timestamp(b) - timestamp(a);
    }

    if (activeSort.value === 'most_voted') {
        return metric(b, 'likes') - metric(a, 'likes') || timestamp(b) - timestamp(a);
    }

    if (activeSort.value === 'controversial') {
        return controversialScore(b) - controversialScore(a) || timestamp(b) - timestamp(a);
    }

    if (activeSort.value === 'questions') {
        return timestamp(b) - timestamp(a);
    }

    return bestScore(b) - bestScore(a) || timestamp(b) - timestamp(a);
};

const bestScore = (node) => {
    const ageHours = Math.max(1, (Date.now() - timestamp(node)) / 36e5);
    const recencyBoost = Math.max(0, 24 - ageHours) / 6;
    return metric(node, 'likes') * 3 + childCount(node) * 1.5 + recencyBoost;
};

const controversialScore = (node) => {
    return childCount(node) * 3 + metric(node, 'likes') * 0.75 + treeDepth(node);
};

const metric = (node, key) => Number(node?.[`${key}_count`] ?? 0);
const childCount = (node) => (node.children || []).length;
const treeDepth = (node) => childCount(node) + (node.children || []).reduce((sum, child) => sum + treeDepth(child), 0);
const timestamp = (node) => new Date(node.created_at || 0).getTime();
const isQuestion = (node) => searchableText(node).includes('?');
const normalize = (value = '') => String(value).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
const searchableText = (node) => normalize([
    node.user?.name,
    node.user?.username,
    node.text,
    node.content,
    node.body,
    stripHtml(node.renderedHtml),
].filter(Boolean).join(' '));
const stripHtml = (value = '') => String(value).replace(/<[^>]*>/g, ' ').replace(/&nbsp;/g, ' ');
</script>
