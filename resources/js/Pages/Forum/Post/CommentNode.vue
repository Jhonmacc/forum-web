<template>
    <div class="comment-node relative">
        <div
            v-if="level > 0"
            class="absolute -left-8 top-5 h-px w-8 bg-gray-200 dark:bg-gray-700"
        ></div>

        <div class="flex items-stretch gap-3">
            <div class="relative flex w-10 shrink-0 flex-col items-center">
                <button @click="$emit('open-user', node.user?.id)" class="relative z-10 shrink-0">
                    <img
                        :src="node.user?.profile_photo_url || fallbackAvatar"
                        :alt="node.user?.name"
                        class="h-10 w-10 rounded-full object-cover border border-gray-200 dark:border-gray-700"
                    >
                </button>

                <button
                    v-if="hasChildren"
                    @click="isExpanded = !isExpanded"
                    class="absolute top-[3.1rem] z-20 flex h-5 w-5 items-center justify-center rounded-full border border-gray-300 bg-white text-[10px] text-gray-500 shadow-sm transition hover:border-accent hover:text-accent dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300"
                    :title="isExpanded ? $t('comments.hide_replies') : $t('comments.view_replies', { count: childCount })"
                >
                    <i :class="isExpanded ? 'fa-solid fa-minus' : 'fa-solid fa-plus'"></i>
                </button>

                <div
                    v-if="hasChildren && isExpanded"
                    class="absolute bottom-0 top-[3.55rem] w-px bg-gray-200 dark:bg-gray-700"
                ></div>
            </div>

            <div class="min-w-0 flex-1 pb-3">
                <article class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm transition hover:border-accent/25 dark:border-gray-700 dark:bg-gray-900">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <button @click="$emit('open-user', node.user?.id)" class="font-extrabold text-sm text-gray-900 dark:text-white hover:text-accent">
                                {{ node.user?.name || $t('profile.user') }}
                            </button>
                            <span class="text-xs text-gray-400 dark:text-gray-500">{{ relativeDate }}</span>
                            <span v-if="isAuthor" class="rounded-full bg-accent/15 px-2 py-0.5 text-[10px] font-black uppercase text-accent">OP</span>
                        </div>

                        <div class="mt-2 text-sm leading-7 text-gray-700 dark:text-gray-200 comment-body" v-html="node.renderedHtml"></div>

                        <div class="mt-3 flex flex-wrap items-center gap-3 text-xs font-bold text-gray-500 dark:text-gray-400">
                            <ReactionPicker
                                :reaction-type="node.reaction_type"
                                :count="node.likes_count || 0"
                                @select="$emit('like', { node, reactionType: $event })"
                            />
                            <button @click="$emit('reply', node)" class="inline-flex items-center gap-1.5 hover:text-accent">
                                <i class="fa-regular fa-comment"></i>
                                {{ $t('comments.reply') }}
                            </button>
                            <button v-if="canManage" @click="$emit('edit', node)" class="inline-flex items-center gap-1.5 hover:text-accent">
                                <i class="fa-solid fa-pen"></i>
                                {{ $t('common.edit') }}
                            </button>
                            <button v-if="canManage" @click="$emit('delete', node)" class="inline-flex items-center gap-1.5 hover:text-red-500">
                                <i class="fa-solid fa-trash"></i>
                                {{ $t('common.delete') }}
                            </button>
                            <span v-if="hasChildren" class="text-gray-400 dark:text-gray-500">
                                {{ childCount }} {{ childCount === 1 ? $t('comments.reply_count_one') : $t('comments.reply_count_many') }}
                            </span>
                        </div>
                    </div>
                </article>

                <div v-if="hasChildren && isExpanded" class="mt-3 space-y-3">
                    <CommentNode
                        v-for="child in node.children"
                        :key="`${child.kind}-${child.id}`"
                        :node="child"
                        :level="level + 1"
                        :current-user-id="currentUserId"
                        :post-author-id="postAuthorId"
                        @like="$emit('like', $event)"
                        @reply="$emit('reply', $event)"
                        @edit="$emit('edit', $event)"
                        @delete="$emit('delete', $event)"
                        @open-user="$emit('open-user', $event)"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import moment from 'moment';
import 'moment/dist/locale/pt-br';
import { useI18n } from 'vue-i18n';
import ReactionPicker from './ReactionPicker.vue';

const props = defineProps({
    node: {
        type: Object,
        required: true,
    },
    level: {
        type: Number,
        default: 0,
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

const { locale } = useI18n();
const isExpanded = ref(true);
const fallbackAvatar = 'https://ui-avatars.com/api/?name=Forum&background=F5B800&color=fff';

const hasChildren = computed(() => childCount.value > 0);
const childCount = computed(() => props.node.children?.length || 0);
const canManage = computed(() => props.currentUserId && props.currentUserId === props.node.user_id);
const isAuthor = computed(() => props.postAuthorId && props.postAuthorId === props.node.user_id);
const relativeDate = computed(() => moment(props.node.created_at).locale(locale.value === 'pt-BR' ? 'pt-br' : 'en').fromNow());
</script>

<style scoped>
.comment-body :deep(.mention-link) {
    border-radius: 9999px;
    background: rgba(245, 184, 0, 0.14);
    color: #d4a000;
    font-weight: 800;
    padding: 0.1rem 0.4rem;
}

.comment-body :deep(.post-reference-card),
.comment-body :deep(.link-preview-card) {
    display: flex;
    gap: 0.85rem;
    margin: 0.75rem 0;
    padding: 0.85rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.95rem;
    background: #f9fafb;
    color: #111827;
    text-decoration: none;
}

.dark .comment-body :deep(.post-reference-card),
.dark .comment-body :deep(.link-preview-card) {
    border-color: #374151;
    background: #111827;
    color: #f9fafb;
}

.comment-body :deep(.post-reference-title),
.comment-body :deep(.link-preview-title) {
    display: block;
    font-weight: 900;
}

.comment-body :deep(.post-reference-meta),
.comment-body :deep(.link-preview-domain),
.comment-body :deep(.link-preview-description) {
    display: block;
    margin-top: 0.15rem;
    color: #6b7280;
    font-size: 0.78rem;
}

.dark .comment-body :deep(.post-reference-meta),
.dark .comment-body :deep(.link-preview-domain),
.dark .comment-body :deep(.link-preview-description) {
    color: #9ca3af;
}

.comment-body :deep(.link-preview-image) {
    width: 86px;
    height: 64px;
    flex-shrink: 0;
    border-radius: 0.75rem;
    object-fit: cover;
}

.comment-body :deep(pre) {
    overflow-x: auto;
    border-radius: 0.75rem;
    background: #111827;
    color: #f9fafb;
    padding: 0.85rem;
}
</style>
