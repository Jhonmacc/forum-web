<template>
    <div class="post-page relative flex h-screen overflow-hidden bg-gray-50 text-gray-900 dark:bg-gray-950 dark:text-gray-100">
        <FloatingSidebarToggle v-if="isAuthenticated" v-model:open="isSidebarOpen" />

        <aside
            v-if="isAuthenticated"
            class="shrink-0 overflow-hidden bg-white transition-all duration-300 ease-in-out dark:bg-gray-900"
            :class="isSidebarOpen
                ? 'w-[248px] border-r border-gray-200 dark:border-gray-700'
                : 'w-0 border-r-0 pointer-events-none'"
            :aria-hidden="!isSidebarOpen"
        >
            <div
                class="flex h-full w-[248px] flex-col overflow-hidden transition-opacity duration-200"
                :class="isSidebarOpen ? 'opacity-100' : 'opacity-0'"
            >
            <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2.5">
                    <div class="w-[34px] h-[34px] rounded-[10px] bg-accent flex items-center justify-center">
                        <i class="fa-solid fa-comments text-white text-sm"></i>
                    </div>
                    <span class="font-extrabold text-base text-gray-900 dark:text-white tracking-tight">
                        {{ $t('forum.forum') }}<span class="text-accent">.</span>
                    </span>
                </div>
            </div>

            <div class="px-4 pt-5 pb-4">
                <button
                    @click="goTo('/forum')"
                    class="w-full py-3 rounded-xl border-none bg-accent text-white font-bold text-sm flex items-center justify-center gap-2 shadow-[0_4px_20px_rgba(245,184,0,0.3)] hover:-translate-y-0.5 hover:shadow-[0_6px_24px_rgba(245,184,0,0.4)] transition-all"
                >
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    {{ $t('post.back_to_forum') }}
                </button>
            </div>

            <nav class="flex-1 overflow-auto px-2.5">
                <p class="text-[10.5px] font-extrabold tracking-[0.1em] text-gray-400 dark:text-gray-500 px-3 py-2 uppercase">
                    {{ $t('forum.navigate') }}
                </p>
                <button
                    v-for="item in navItems"
                    :key="item.id"
                    @click="goTo(item.href)"
                    class="w-full flex items-center gap-2.5 px-3.5 py-2.5 rounded-[10px] border-none cursor-pointer text-sm mb-0.5 transition-all text-left"
                    :class="item.active
                        ? 'bg-accent/15 text-accent font-bold'
                        : 'bg-transparent text-gray-500 dark:text-gray-400 font-medium hover:bg-gray-100 dark:hover:bg-gray-800'"
                >
                    <i :class="item.icon" class="w-4 text-center"></i>
                    {{ item.label }}
                </button>

                <div class="h-px bg-gray-200 dark:bg-gray-700 mx-3 my-4"></div>

                <p class="text-[10.5px] font-extrabold tracking-[0.1em] text-gray-400 dark:text-gray-500 px-3 py-2 uppercase">
                    {{ $t('post.context') }}
                </p>
                <div class="mx-2 rounded-2xl bg-gray-50 dark:bg-gray-800 p-3">
                    <div class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase">{{ $t('profile.votes') }}</div>
                    <div class="mt-1 text-2xl font-extrabold text-gray-900 dark:text-white">{{ post?.likes_count || 0 }}</div>
                    <div class="mt-3 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase">{{ $t('comments.comments') }}</div>
                    <div class="mt-1 text-2xl font-extrabold text-gray-900 dark:text-white">{{ commentsTree.length }}</div>
                </div>
            </nav>

            <div class="px-4 py-3.5 border-t border-gray-200 dark:border-gray-700 flex items-center gap-2.5">
                <div class="w-[34px] h-[34px] rounded-full overflow-hidden shrink-0">
                    <img class="w-full h-full object-cover" :src="authUser.profile_photo_url" :alt="authUser.name">
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-[13px] font-bold text-gray-900 dark:text-white truncate">{{ authUser.name }}</div>
                </div>
                <a :href="route('profile.show')" class="text-gray-400 hover:text-gray-700 dark:hover:text-white transition-colors text-base">
                    <i class="fa-solid fa-gear"></i>
                </a>
            </div>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <Header v-if="isAuthenticated" />
            <PublicHeader v-else :is-authenticated="false" @open-auth="openAuth" />

            <main class="flex-1 overflow-auto p-4 sm:p-6 lg:p-7">
                <div v-if="isLoading" class="flex h-64 items-center justify-center text-gray-400">
                    <i class="fa-solid fa-spinner fa-spin text-2xl"></i>
                </div>

                <div v-else-if="post" class="mx-auto max-w-5xl">
                    <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <PostHero
                            :post="post"
                            :relative-date="formatRelativeTime(post.created_at)"
                            :can-manage="canManagePost"
                            :comments-count="commentsTree.length"
                            @vote="toggleLikePost"
                            @edit="openEditPostModal"
                            @delete="deletePost"
                            @open-user="openUser"
                        />

                        <PostContent :content="post.description" />

                        <CommentThread
                            :comments="commentsTree"
                            :current-user-id="currentUserId"
                            :post-author-id="post.user_id"
                            @like="toggleLikeNode"
                            @reply="openReplyComposer"
                            @edit="openEditNode"
                            @delete="deleteNode"
                            @open-user="openUser"
                        >
                            <template #composer>
                                <RichTextComposer
                                    v-if="isAuthenticated"
                                    v-model="newComment"
                                    :user="$page.props.auth.user"
                                    :placeholder="$t('comments.participate_conversation')"
                                    :collapsed-placeholder="$t('comments.participate_conversation')"
                                    :submit-label="$t('comments.comment_button')"
                                    :processing="isSubmittingComment"
                                    :max-length="COMMENT_MAX_LENGTH"
                                    collapsible
                                    @submit="addComment"
                                />
                                <button
                                    v-else
                                    type="button"
                                    @click="openAuth('login')"
                                    class="w-full rounded-full border border-gray-200 bg-white px-5 py-4 text-left text-sm font-medium text-gray-500 transition hover:border-accent/60 hover:text-gray-700 focus:border-accent focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:hover:text-gray-200"
                                >
                                    {{ $t('comments.participate_conversation') }}
                                </button>
                            </template>
                        </CommentThread>
                    </article>
                </div>
            </main>
        </div>

        <div v-if="replyTarget" class="fixed inset-0 z-[100] bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="w-full max-w-2xl rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-2xl p-5">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-extrabold text-gray-900 dark:text-white">{{ $t('comments.reply') }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $t('comments.replying_to', { name: replyTarget.user?.name || $t('profile.user') }) }}</p>
                    </div>
                    <button @click="closeReplyComposer" class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <RichTextComposer
                    v-model="replyBody"
                    :user="$page.props.auth.user"
                    :placeholder="$t('comments.replying_to', { name: replyTarget.user?.name || $t('profile.user') })"
                    :submit-label="$t('comments.send_reply')"
                    :processing="isSubmittingReply"
                    :max-length="COMMENT_MAX_LENGTH"
                    show-cancel
                    @submit="submitReply"
                    @cancel="closeReplyComposer"
                />
            </div>
        </div>

        <div v-if="editTarget" class="fixed inset-0 z-[100] bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="w-full max-w-2xl rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-2xl p-5">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-extrabold text-gray-900 dark:text-white">
                            {{ editTarget.kind === 'comment' ? $t('comments.edit_comment') : $t('comments.edit_reply') }}
                        </h3>
                    </div>
                    <button @click="closeEditNodeComposer" class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <RichTextComposer
                    v-model="editBody"
                    :user="$page.props.auth.user"
                    :placeholder="editTarget.kind === 'comment' ? $t('comments.edit_comment') : $t('comments.edit_reply')"
                    :submit-label="editTarget.kind === 'comment' ? $t('comments.update_comment') : $t('comments.update_reply')"
                    :processing="isSubmittingEdit"
                    :max-length="COMMENT_MAX_LENGTH"
                    show-cancel
                    @submit="submitEditNode"
                    @cancel="closeEditNodeComposer"
                />
            </div>
        </div>

        <div v-if="showEditModal" class="fixed inset-0 z-[100] bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="w-full max-w-4xl max-h-[90vh] flex flex-col rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-2xl overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-lg font-extrabold text-gray-900 dark:text-white">{{ $t('post.edit_post') }}</h2>
                    <button @click="closeEditPostModal" class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto space-y-5">
                    <div>
                        <label class="block text-xs font-bold tracking-wider text-gray-500 dark:text-gray-400 uppercase mb-2">{{ $t('forum.title_label') }}</label>
                        <input v-model="postToEdit.title" :maxlength="POST_TITLE_MAX_LENGTH" class="form-input" :class="{ 'border-red-400': errors.title }">
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ postToEdit.title.length }} / {{ POST_TITLE_MAX_LENGTH }} {{ $t('comments.characters') }}</p>
                        <p v-if="errors.title" class="form-error">{{ errors.title }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold tracking-wider text-gray-500 dark:text-gray-400 uppercase mb-2">{{ $t('forum.description_label') }}</label>
                        <TextQuill ref="textQuill" v-model:content="postToEdit.description" mode="post" :max-plain-text-length="POST_DESCRIPTION_MAX_LENGTH" />
                        <p v-if="errors.description" class="form-error">{{ errors.description }}</p>
                    </div>
                    <div>
                        <button @click="showTaggingModal = true" class="px-4 py-2.5 rounded-xl bg-gray-600 text-white text-sm font-bold hover:bg-gray-700">
                            {{ $t('forum.choose_tags') }}
                        </button>
                    </div>
                    <div v-if="postToEdit.tags.length" class="flex flex-wrap gap-2">
                        <span
                            v-for="tag in postToEdit.tags"
                            :key="tag.id"
                            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-black uppercase text-white"
                            :style="{ backgroundColor: tag.color || '#999' }"
                        >
                            <i :class="tag.icon || 'fa-solid fa-tag'"></i>
                            {{ tag.name }}
                        </span>
                    </div>
                    <p v-if="errors.tags" class="form-error">{{ errors.tags }}</p>
                </div>

                <div class="px-6 py-5 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                    <button @click="closeEditPostModal" class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-sm font-bold text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                        {{ $t('common.cancel') }}
                    </button>
                    <button @click="updatePost" class="px-5 py-2.5 rounded-xl bg-accent text-white text-sm font-bold hover:bg-accent-dark">
                        {{ $t('post.update_post') }}
                    </button>
                </div>

                <TaggingModal v-if="showTaggingModal" :selected-tags="postToEdit.tags" @close="showTaggingModal = false" @select-tags="setTags" />
            </div>
        </div>

        <AuthModal :show="showAuthModal" :initial-tab="authTab" @close="closeAuth" />
    </div>
</template>

<script setup>
import Header from '@/Components/Header.vue';
import PublicHeader from '@/Components/PublicHeader.vue';
import AuthModal from '@/Components/AuthModal.vue';
import TextQuill from '@/Components/TextQuill.vue';
import FloatingSidebarToggle from '@/Components/FloatingSidebarToggle.vue';
import axios from 'axios';
import moment from 'moment';
import 'moment/dist/locale/pt-br';
import Swal from 'sweetalert2';
import { computed, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import PostHero from './Post/PostHero.vue';
import PostContent from './Post/PostContent.vue';
import RichTextComposer from './Post/RichTextComposer.vue';
import CommentThread from './Post/CommentThread.vue';
import TaggingModal from './TaggingModal.vue';

const props = defineProps({
    post: Object,
});

const COMMENT_MAX_LENGTH = 2000;
const POST_TITLE_MAX_LENGTH = 120;
const POST_DESCRIPTION_MAX_LENGTH = 10000;
const POST_DESCRIPTION_HTML_MAX_LENGTH = 20000;

const { t, locale } = useI18n();
const page = usePage();

const post = ref(null);
const isLoading = ref(true);
const newComment = ref('');
const replyBody = ref('');
const replyTarget = ref(null);
const editBody = ref('');
const editTarget = ref(null);
const isSubmittingComment = ref(false);
const isSubmittingReply = ref(false);
const isSubmittingEdit = ref(false);
const showEditModal = ref(false);
const showTaggingModal = ref(false);
const showAuthModal = ref(false);
const authTab = ref('login');
const postToEdit = ref({ title: '', description: '', tags: [] });
const errors = ref({});
const textQuill = ref(null);
const isSidebarOpen = ref(true);

const authUser = computed(() => page.props.auth.user || null);
const currentUserId = computed(() => authUser.value?.id || null);
const isAuthenticated = computed(() => Boolean(authUser.value));
const canManagePost = computed(() => currentUserId.value && post.value?.user_id === currentUserId.value);
const commentsTree = computed(() => post.value?.comments || []);

const navItems = computed(() => [
    ...(authUser.value?.is_admin ? [{ id: 'dashboard', label: t('forum.dashboard'), href: '/dashboard', icon: 'fa-solid fa-table-cells-large', active: false }] : []),
    { id: 'forum', label: t('forum.forum'), href: '/forum', icon: 'fa-solid fa-comments', active: true },
    ...(authUser.value?.is_admin ? [{ id: 'tags', label: t('tags.manage_tags'), href: '/forum/tags', icon: 'fa-solid fa-tags', active: false }] : []),
    ...(authUser.value ? [{ id: 'meus', label: t('forum.my_posts'), href: `/users/${currentUserId.value}`, icon: 'fa-solid fa-user', active: false }] : []),
]);

const openAuth = (tab = 'login') => {
    authTab.value = tab === 'register' ? 'register' : 'login';
    showAuthModal.value = true;
};

const closeAuth = () => {
    showAuthModal.value = false;
};

const requireAuth = (tab = 'login') => {
    if (isAuthenticated.value) return true;
    openAuth(tab);
    return false;
};

const fetchPost = async () => {
    isLoading.value = true;
    try {
        const id = window.location.pathname.split('/')[2];
        const response = await axios.get(`/posts/${id}`);
        post.value = normalizePost(response.data);
    } catch (error) {
        showAlert('error', t('common.error'), error.response?.data?.message || t('forum.unexpected_error'));
    } finally {
        isLoading.value = false;
    }
};

const normalizePost = (rawPost) => ({
    ...rawPost,
    liked: Boolean(rawPost.liked_by_current_user || rawPost.likes?.some(like => like.user_id === currentUserId.value)),
    likes_count: rawPost.likes_count ?? rawPost.likes?.length ?? 0,
    comments: (rawPost.comments || []).map(comment => normalizeComment(comment)),
});

const normalizeComment = (comment) => ({
    ...comment,
    kind: 'comment',
    text: comment.content,
    renderedHtml: renderRichContent(comment.content),
    liked: Boolean(comment.liked_by_current_user || comment.likes?.some(like => like.user_id === currentUserId.value)),
    likes_count: comment.likes_count ?? comment.likes?.length ?? 0,
    children: (comment.replies || []).map(reply => normalizeReply(reply)),
});

const normalizeReply = (reply) => ({
    ...reply,
    kind: 'reply',
    text: reply.body,
    renderedHtml: renderRichContent(reply.body),
    liked: Boolean(reply.liked_by_current_user || reply.likes?.some(like => like.user_id === currentUserId.value)),
    likes_count: reply.likes_count ?? reply.likes?.length ?? 0,
    children: (reply.children || []).map(child => normalizeReply(child)),
});

const renderRichContent = (content = '') => {
    const value = String(content || '');
    const hasHtml = /<\/?[a-z][\s\S]*>/i.test(value);
    const html = hasHtml
        ? value
        : value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/\n/g, '<br>');

    return html.replace(/(^|[\s>])@([a-zA-Z0-9._]+)/g, '$1<span class="mention-link">@$2</span>');
};

const addComment = async ({ body, mentions }) => {
    if (!requireAuth('login')) return;
    if (!body.trim() || !post.value) return;
    isSubmittingComment.value = true;
    try {
        const response = await axios.post(`/posts/${post.value.id}/comments`, {
            content: body,
            post_id: post.value.id,
            mentions,
        });

        post.value.comments.push(normalizeComment({ ...response.data.data, replies: [], likes: [] }));
        newComment.value = '';
        showAlert('success', t('comments.comment_added'));
    } catch (error) {
        showAlert('error', t('comments.error_add_comment'), error.response?.data?.message || t('forum.unexpected_error'));
    } finally {
        isSubmittingComment.value = false;
    }
};

const openReplyComposer = (node) => {
    if (!requireAuth('login')) return;
    replyTarget.value = node;
    replyBody.value = node.user?.username ? `@${node.user.username} ` : '';
};

const closeReplyComposer = () => {
    replyTarget.value = null;
    replyBody.value = '';
};

const submitReply = async ({ body, mentions }) => {
    if (!requireAuth('login')) return;
    if (!replyTarget.value || !body.trim()) return;
    isSubmittingReply.value = true;
    try {
        const endpoint = replyTarget.value.kind === 'comment'
            ? `/comments/${replyTarget.value.id}/reply`
            : `/replies/${replyTarget.value.id}/reply`;
        const response = await axios.post(endpoint, { body, mentions });
        const reply = normalizeReply({ ...response.data, likes: [], children: [] });
        replyTarget.value.children = [...(replyTarget.value.children || []), reply];
        closeReplyComposer();
        showAlert('success', t('comments.reply_sent'));
    } catch (error) {
        showAlert('error', t('comments.error_send_reply'), error.response?.data?.message || t('forum.unexpected_error'));
    } finally {
        isSubmittingReply.value = false;
    }
};

const toggleLikePost = async () => {
    if (!requireAuth('login')) return;
    const previousLiked = post.value.liked;
    const previousCount = post.value.likes_count || 0;
    post.value.liked = !previousLiked;
    post.value.liked_by_current_user = post.value.liked;
    post.value.likes_count = Math.max(0, previousCount + (previousLiked ? -1 : 1));
    try {
        const response = await axios.post(`/posts/${post.value.id}/like`);
        post.value.liked = response.data.liked;
        post.value.liked_by_current_user = response.data.liked;
        post.value.likes_count = response.data.likes_count;
    } catch (error) {
        post.value.liked = previousLiked;
        post.value.liked_by_current_user = previousLiked;
        post.value.likes_count = previousCount;
        showAlert('error', t('common.error'), t('post.error_like_post'));
    }
};

const toggleLikeNode = async (node) => {
    if (!requireAuth('login')) return;
    const previousLiked = node.liked;
    const previousCount = node.likes_count || 0;
    node.liked = !previousLiked;
    node.likes_count = Math.max(0, previousCount + (previousLiked ? -1 : 1));
    try {
        const endpoint = node.kind === 'comment' ? `/comments/${node.id}/like` : `/replies/${node.id}/like`;
        const response = await axios.post(endpoint);
        node.liked = response.data.liked;
        node.likes_count = response.data.likes_count;
    } catch (error) {
        node.liked = previousLiked;
        node.likes_count = previousCount;
        showAlert('error', t('common.error'), node.kind === 'comment' ? t('comments.error_like_comment') : t('comments.error_like_reply'));
    }
};

const openEditNode = (node) => {
    editTarget.value = node;
    editBody.value = node.kind === 'comment' ? node.content : node.body;
};

const closeEditNodeComposer = () => {
    editTarget.value = null;
    editBody.value = '';
};

const submitEditNode = async ({ body, mentions }) => {
    if (!editTarget.value || !body?.trim()) return;
    const node = editTarget.value;
    isSubmittingEdit.value = true;
    try {
        if (node.kind === 'comment') {
            await axios.put(`/comments/${node.id}`, { content: body, mentions });
            node.content = body;
        } else {
            await axios.put(`/replies/${node.id}`, { body, mentions });
            node.body = body;
        }
        node.text = body;
        node.renderedHtml = renderRichContent(body);
        closeEditNodeComposer();
        showAlert('success', node.kind === 'comment' ? t('comments.comment_updated') : t('comments.reply_updated'));
    } catch (error) {
        showAlert('error', node.kind === 'comment' ? t('comments.error_update_comment') : t('comments.error_update_reply'), error.response?.data?.message || t('forum.unexpected_error'));
    } finally {
        isSubmittingEdit.value = false;
    }
};

const deleteNode = async (node) => {
    const result = await confirmDanger(node.kind === 'comment' ? t('comments.confirm_delete_comment') : t('comments.confirm_delete_reply'));
    if (!result) return;

    try {
        await axios.delete(node.kind === 'comment' ? `/comments/${node.id}` : `/replies/${node.id}`);
        removeNode(node);
        showAlert('success', node.kind === 'comment' ? t('comments.comment_deleted') : t('comments.reply_deleted'));
    } catch (error) {
        showAlert('error', node.kind === 'comment' ? t('comments.error_delete_comment') : t('comments.error_delete_reply'), error.response?.data?.message || t('forum.unexpected_error'));
    }
};

const removeNode = (node) => {
    if (node.kind === 'comment') {
        post.value.comments = post.value.comments.filter(comment => comment.id !== node.id);
        return;
    }

    const removeFromChildren = (items) => {
        for (const item of items) {
            const before = item.children?.length || 0;
            item.children = (item.children || []).filter(child => child.id !== node.id);
            if (item.children.length !== before) return true;
            if (removeFromChildren(item.children)) return true;
        }
        return false;
    };

    removeFromChildren(post.value.comments);
};

const openEditPostModal = () => {
    postToEdit.value = {
        title: post.value.title || '',
        description: post.value.description || '',
        tags: [...(post.value.tags || [])],
    };
    errors.value = {};
    showEditModal.value = true;
};

const closeEditPostModal = () => {
    showEditModal.value = false;
    textQuill.value?.clearContent?.();
    postToEdit.value = { title: '', description: '', tags: [] };
};

const updatePost = async () => {
    if (!validatePost()) return;

    try {
        await axios.put(`/posts/${post.value.id}`, {
            title: postToEdit.value.title,
            description: postToEdit.value.description,
            tags: postToEdit.value.tags.map(tag => tag.id),
        });
        showAlert('success', t('post.post_updated'));
        closeEditPostModal();
        await fetchPost();
    } catch (error) {
        showAlert('error', t('post.post_update_error'), error.response?.data?.message || t('forum.unexpected_error'));
    }
};

const validatePost = () => {
    errors.value = {};
    if (!postToEdit.value.title.trim()) errors.value.title = t('forum.title_required');
    if (postToEdit.value.title.length > POST_TITLE_MAX_LENGTH) errors.value.title = t('forum.title_too_long', { max: POST_TITLE_MAX_LENGTH });
    if (!getPlainText(postToEdit.value.description).trim()) errors.value.description = t('forum.description_required');
    if (getPlainText(postToEdit.value.description).length > POST_DESCRIPTION_MAX_LENGTH || String(postToEdit.value.description).length > POST_DESCRIPTION_HTML_MAX_LENGTH) {
        errors.value.description = t('forum.description_too_long', { max: POST_DESCRIPTION_MAX_LENGTH });
    }
    if (!postToEdit.value.tags.length) errors.value.tags = t('forum.tags_required');
    return Object.keys(errors.value).length === 0;
};

const deletePost = async () => {
    const result = await confirmDanger(t('post.confirm_delete_post'));
    if (!result) return;

    try {
        await axios.delete(`/posts/${post.value.id}`);
        await showAlert('success', t('post.post_deleted'));
        router.get('/forum');
    } catch (error) {
        showAlert('error', t('post.post_delete_error'), error.response?.data?.message || t('forum.unexpected_error'));
    }
};

const setTags = (tags) => {
    postToEdit.value.tags = tags.map(tag => ({
        id: parseInt(tag.id, 10),
        name: tag.name || '',
        code: tag.code || null,
        color: tag.color || '#ccc',
        icon: tag.icon || 'fa-solid fa-tag',
        description: tag.description || '',
    }));
    showTaggingModal.value = false;
};

const extractMentions = (value) => {
    const usernames = [...String(value).matchAll(/@([a-zA-Z0-9._]+)/g)].map(match => match[1]);
    return usernames.map(username => ({ username, name: username }));
};

const getPlainText = (value = '') => String(value)
    .replace(/<[^>]*>/g, ' ')
    .replace(/&nbsp;/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();

const confirmDanger = async (title) => {
    const result = await Swal.fire({
        icon: 'warning',
        title,
        showCancelButton: true,
        confirmButtonText: t('common.yes_delete'),
        cancelButtonText: t('common.cancel'),
        reverseButtons: true,
        background: isDarkMode() ? '#111827' : '#ffffff',
        color: isDarkMode() ? '#f9fafb' : '#111827',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
    });

    return result.isConfirmed;
};

const showAlert = (icon, title, text = null) => Swal.fire({
    icon,
    title,
    text,
    timer: icon === 'success' ? 1600 : undefined,
    showConfirmButton: icon !== 'success',
    background: isDarkMode() ? '#111827' : '#ffffff',
    color: isDarkMode() ? '#f9fafb' : '#111827',
});

const formatRelativeTime = (date) => moment(date).locale(locale.value === 'pt-BR' ? 'pt-br' : 'en').fromNow();
const openUser = (id) => {
    if (!id) return;
    if (!requireAuth('login')) return;
    router.get(`/users/${id}`);
};
const goTo = (href) => router.get(href);
const isDarkMode = () => document.documentElement.classList.contains('dark');

fetchPost();
</script>

<style scoped>
.post-page {
    color-scheme: light;
}

:global(.dark) .post-page {
    color-scheme: dark;
}

.form-input {
    width: 100%;
    border-radius: 0.75rem;
    border: 1.5px solid rgb(229 231 235);
    background: rgb(249 250 251);
    padding: 0.7rem 0.9rem;
    font-size: 0.875rem;
    color: rgb(17 24 39);
    outline: none;
}

.form-input:focus {
    border-color: #F5B800;
    background: white;
}

:global(.dark) .form-input {
    border-color: rgb(55 65 81);
    background: rgb(31 41 55);
    color: rgb(243 244 246);
}

.form-error {
    margin-top: 0.35rem;
    font-size: 0.8rem;
    color: rgb(239 68 68);
}
</style>
