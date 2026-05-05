<template>
    <div class="profile-page relative flex h-screen overflow-hidden bg-gray-50 text-gray-900 dark:bg-gray-950 dark:text-gray-100">
        <FloatingSidebarToggle :open="!isSidebarCollapsed" @update:open="isSidebarCollapsed = !$event" />

        <aside
            class="shrink-0 overflow-hidden bg-white transition-all duration-300 ease-in-out dark:bg-gray-900"
            :class="isSidebarCollapsed ? 'w-0 border-r-0 pointer-events-none' : 'w-[248px] border-r border-gray-200 dark:border-gray-700'"
            :aria-hidden="isSidebarCollapsed"
        >
            <div class="flex h-full w-[248px] flex-col overflow-hidden transition-opacity duration-200" :class="isSidebarCollapsed ? 'opacity-0' : 'opacity-100'">
            <div
                class="py-5 border-b border-gray-200 dark:border-gray-700"
                :class="isSidebarCollapsed ? 'px-3' : 'px-6'"
            >
                <div class="flex items-center gap-2.5" :class="{ 'justify-center': isSidebarCollapsed }">
                    <div class="w-[34px] h-[34px] rounded-[10px] bg-accent flex items-center justify-center">
                        <i class="fa-solid fa-comments text-white text-sm"></i>
                    </div>
                    <span v-if="!isSidebarCollapsed" class="font-extrabold text-base text-gray-900 dark:text-white tracking-tight">
                        {{ $t('forum.forum') }}<span class="text-accent">.</span>
                    </span>
                    <button
                        v-if="!isSidebarCollapsed"
                        @click="isSidebarCollapsed = true"
                        class="ml-auto w-8 h-8 rounded-xl text-gray-400 hover:text-accent hover:bg-accent/10 transition-colors"
                        :title="$t('profile.collapse_sidebar')"
                    >
                        <i class="fa-solid fa-angles-left text-xs"></i>
                    </button>
                </div>
                <button
                    v-if="isSidebarCollapsed"
                    @click="isSidebarCollapsed = false"
                    class="mt-4 w-full h-9 rounded-xl text-gray-400 hover:text-accent hover:bg-accent/10 transition-colors"
                    :title="$t('profile.expand_sidebar')"
                >
                    <i class="fa-solid fa-angles-right text-xs"></i>
                </button>
            </div>

            <div class="px-4 pt-5 pb-4">
                <button
                    @click="showCreatePostModal = true"
                    class="w-full py-3 rounded-xl border-none bg-accent text-white font-bold text-sm flex items-center justify-center gap-2 shadow-[0_4px_20px_rgba(245,184,0,0.3)] hover:-translate-y-0.5 hover:shadow-[0_6px_24px_rgba(245,184,0,0.4)] transition-all"
                    :title="isSidebarCollapsed ? $t('forum.new_discussion') : null"
                >
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span v-if="!isSidebarCollapsed">{{ $t('forum.new_discussion') }}</span>
                </button>
            </div>

            <nav class="flex-1 overflow-auto px-2.5">
                <p v-if="!isSidebarCollapsed" class="text-[10.5px] font-extrabold tracking-[0.1em] text-gray-400 dark:text-gray-500 px-3 py-2 uppercase">
                    {{ $t('forum.navigate') }}
                </p>
                <button
                    v-for="item in navItems"
                    :key="item.id"
                    @click="goTo(item.href)"
                    class="w-full flex items-center gap-2.5 rounded-[10px] border-none cursor-pointer text-sm mb-0.5 transition-all text-left"
                    :class="[
                        isSidebarCollapsed ? 'justify-center px-0 py-3' : 'px-3.5 py-2.5',
                        item.active
                            ? 'bg-accent/15 text-accent font-bold'
                            : 'bg-transparent text-gray-500 dark:text-gray-400 font-medium hover:bg-gray-100 dark:hover:bg-gray-800'
                    ]"
                    :title="isSidebarCollapsed ? item.label : null"
                >
                    <i :class="item.icon" class="w-4 text-center"></i>
                    <span v-if="!isSidebarCollapsed">{{ item.label }}</span>
                </button>

                <div class="h-px bg-gray-200 dark:bg-gray-700 mx-3 my-4"></div>

                <p v-if="!isSidebarCollapsed" class="text-[10.5px] font-extrabold tracking-[0.1em] text-gray-400 dark:text-gray-500 px-3 py-2 uppercase">
                    {{ $t('profile.quick_filters') }}
                </p>
                <button
                    v-for="filter in quickFilters"
                    :key="filter.id"
                    @click="activeFilter = filter.id"
                    class="w-full flex items-center gap-2.5 rounded-[10px] border-none cursor-pointer text-[13.5px] mb-0.5 transition-all text-left"
                    :class="[
                        isSidebarCollapsed ? 'justify-center px-0 py-3 relative' : 'px-3.5 py-2',
                        activeFilter === filter.id
                            ? 'bg-accent/15 text-accent font-bold'
                            : 'bg-transparent text-gray-500 dark:text-gray-400 font-medium hover:bg-gray-100 dark:hover:bg-gray-800'
                    ]"
                    :title="isSidebarCollapsed ? `${filter.label}: ${filter.count}` : null"
                >
                    <i :class="filter.icon" class="w-4 text-center"></i>
                    <span v-if="!isSidebarCollapsed">{{ filter.label }}</span>
                    <span
                        class="text-[11px] font-bold px-1.5 py-0.5 rounded-full"
                        :class="[
                            isSidebarCollapsed ? 'absolute -right-0.5 -top-0.5' : 'ml-auto',
                            activeFilter === filter.id ? 'bg-accent/25 text-accent' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400'
                        ]"
                    >
                        {{ filter.count }}
                    </span>
                </button>
            </nav>

            <div class="px-4 py-3.5 border-t border-gray-200 dark:border-gray-700 flex items-center gap-2.5" :class="{ 'justify-center': isSidebarCollapsed }">
                <div class="w-[34px] h-[34px] rounded-full overflow-hidden shrink-0">
                    <img
                        class="w-full h-full object-cover"
                        :src="$page.props.auth.user.profile_photo_url"
                        :alt="$page.props.auth.user.name"
                        :title="isSidebarCollapsed ? $page.props.auth.user.name : null"
                    >
                </div>
                <div v-if="!isSidebarCollapsed" class="flex-1 min-w-0">
                    <div class="text-[13px] font-bold text-gray-900 dark:text-white truncate">{{ $page.props.auth.user.name }}</div>
                </div>
                <a v-if="!isSidebarCollapsed" :href="route('profile.show')" class="text-gray-400 hover:text-gray-700 dark:hover:text-white transition-colors text-base">
                    <i class="fa-solid fa-gear"></i>
                </a>
            </div>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <Header />

            <main class="flex-1 overflow-auto p-4 sm:p-6 lg:p-7">
                <section class="mb-6">
                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 mb-5">
                        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center min-w-0">
                                <img
                                    :src="user.profile_photo_url"
                                    :alt="user.name"
                                    class="w-20 h-20 rounded-2xl object-cover border border-gray-200 dark:border-gray-700 shrink-0"
                                >
                                <div class="min-w-0">
                                    <div class="inline-flex items-center gap-2 rounded-full bg-accent/15 px-3 py-1 text-xs font-bold text-accent mb-3">
                                        <i class="fa-solid fa-user"></i>
                                        {{ isOwner ? $t('profile.my_profile') : $t('profile.public_profile') }}
                                    </div>
                                    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white leading-tight truncate">{{ user.name }}</h1>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $t('profile.member_since', { date: formatRelativeTime(user.created_at) }) }}
                                    </p>
                                </div>
                            </div>

                            <div class="w-full lg:w-[360px] rounded-2xl bg-gray-50 dark:bg-gray-800 p-4">
                                <div class="text-xs font-bold tracking-wider text-gray-400 dark:text-gray-500 uppercase">
                                    {{ $t('profile.best_post') }}
                                </div>
                                <div v-if="stats.best_post" class="mt-2">
                                    <button @click="goTo(`/posts/${stats.best_post.id}/edit`)" class="text-left font-extrabold text-gray-900 dark:text-white hover:text-accent line-clamp-2">
                                        {{ stats.best_post.title }}
                                    </button>
                                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        {{ stats.best_post.likes_count }} {{ $t('profile.votes') }}
                                    </div>
                                </div>
                                <div v-else class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $t('profile.no_best_post') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Settings -->
                    <div v-if="$page.props.auth.user.is_admin && isOwner" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 mb-5">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-9 h-9 rounded-xl bg-accent/15 text-accent flex items-center justify-center">
                                <i class="fa-solid fa-gear"></i>
                            </div>
                            <h3 class="font-extrabold text-gray-900 dark:text-white">{{ $t('profile.admin_settings') }}</h3>
                        </div>
                        <form @submit.stop.prevent="saveSettings" class="flex flex-col sm:flex-row items-end gap-3">
                            <div class="flex-1 w-full">
                                <label class="block text-xs font-bold tracking-wider text-gray-400 dark:text-gray-500 uppercase mb-1.5">
                                    {{ $t('profile.forum_name_label') }}
                                </label>
                                <input
                                    v-model="forumNameInput"
                                    type="text"
                                    maxlength="50"
                                    :placeholder="$t('profile.forum_name_placeholder')"
                                    class="w-full py-2.5 px-4 rounded-xl border-[1.5px] border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-sm text-gray-900 dark:text-gray-100 outline-none focus:border-accent transition-colors"
                                />
                            </div>
                            <button
                                type="submit"
                                :disabled="savingSettings"
                                class="px-6 py-2.5 rounded-xl border-none bg-accent text-white font-bold text-sm cursor-pointer shadow-[0_4px_16px_rgba(245,184,0,0.35)] hover:bg-accent-dark transition-colors disabled:opacity-50 whitespace-nowrap"
                            >
                                {{ savingSettings ? '...' : $t('profile.save_settings') }}
                            </button>
                        </form>
                        <p v-if="settingsSaved" class="text-green-500 text-xs font-bold mt-2">{{ $t('profile.settings_saved') }}</p>
                    </div>

                    <div>
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between mb-5">
                            <div>
                                <div class="inline-flex items-center gap-2 rounded-full bg-accent/15 px-3 py-1 text-xs font-bold text-accent mb-3">
                                    <i class="fa-solid fa-timeline"></i>
                                    {{ isOwner ? $t('profile.my_posts') : $t('profile.profile_posts') }}
                                </div>
                                <h2 class="text-[26px] font-extrabold tracking-tight text-gray-900 dark:text-white">
                                    {{ $t('profile.posts_by', { name: user.name || $t('profile.user') }) }}
                                </h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $t('profile.subtitle') }}
                                </p>
                            </div>
                            <button
                                v-if="isOwner"
                                @click="showCreatePostModal = true"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-accent text-white text-sm font-bold shadow-[0_4px_16px_rgba(245,184,0,0.35)] hover:bg-accent-dark transition-colors"
                            >
                                <i class="fa-solid fa-plus text-xs"></i>
                                {{ $t('forum.new_discussion') }}
                            </button>
                        </div>

                        <section class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-5">
                            <div v-for="metric in metrics" :key="metric.id" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl px-5 py-4">
                                <div class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ metric.label }}</div>
                                <div class="mt-2 text-2xl font-extrabold text-gray-900 dark:text-white">{{ metric.value }}</div>
                            </div>
                        </section>

                        <section class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden">
                            <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                                <div class="relative flex-1 max-w-xl">
                                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                    <input
                                        v-model="search"
                                        type="search"
                                        :placeholder="$t('profile.search_placeholder')"
                                        class="w-full rounded-xl border-[1.5px] border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 pl-10 pr-4 text-sm text-gray-900 dark:text-gray-100 outline-none focus:border-accent placeholder-gray-400 dark:placeholder-gray-500"
                                    >
                                </div>

                                <div class="flex items-center gap-2">
                                    <select
                                        v-model="sortBy"
                                        class="rounded-xl border-[1.5px] border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-3 text-sm font-semibold text-gray-600 dark:text-gray-300 focus:border-accent"
                                    >
                                        <option value="hot">{{ $t('profile.sort_hot') }}</option>
                                        <option value="recent">{{ $t('profile.sort_recent') }}</option>
                                        <option value="votes">{{ $t('profile.sort_votes') }}</option>
                                        <option value="comments">{{ $t('profile.sort_comments') }}</option>
                                    </select>
                                    <button
                                        @click="sortDirection = sortDirection === 'asc' ? 'desc' : 'asc'"
                                        class="w-10 h-10 rounded-xl border-[1.5px] border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800"
                                        :title="$t('profile.toggle_sort')"
                                    >
                                        <i :class="sortDirection === 'asc' ? 'fa-solid fa-arrow-up-short-wide' : 'fa-solid fa-arrow-down-wide-short'"></i>
                                    </button>
                                </div>
                            </div>

                            <div v-if="timelineGroups.length" class="divide-y divide-gray-100 dark:divide-gray-800">
                                <section v-for="group in timelineGroups" :key="group.id" class="p-5">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-9 h-9 rounded-xl bg-accent/15 text-accent flex items-center justify-center">
                                            <i :class="group.icon"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-extrabold text-gray-900 dark:text-white">{{ group.label }}</h3>
                                            <p class="text-xs text-gray-400 dark:text-gray-500">{{ group.posts.length }} {{ group.posts.length === 1 ? $t('profile.post') : $t('profile.posts') }}</p>
                                        </div>
                                    </div>

                                    <div class="space-y-3">
                                        <article
                                            v-for="post in group.posts"
                                            :key="post.id"
                                            class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60 p-4 transition-colors hover:border-accent/40"
                                        >
                                            <div class="flex gap-4">
                                                <button
                                                    @click.stop="toggleVote(post)"
                                                    class="w-12 shrink-0 rounded-2xl border flex flex-col items-center justify-center py-2 transition-colors"
                                                    :class="post.liked_by_current_user
                                                        ? 'border-accent bg-accent/15 text-accent'
                                                        : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-400 hover:text-accent hover:border-accent/40'"
                                                    :title="$t('profile.upvote')"
                                                >
                                                    <i class="fa-solid fa-arrow-up text-sm"></i>
                                                    <span class="text-sm font-extrabold mt-1">{{ post.likes_count || 0 }}</span>
                                                </button>

                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <button @click="goTo(`/posts/${post.id}/edit`)" class="text-left min-w-0">
                                                            <h4 class="text-lg font-extrabold text-gray-900 dark:text-white hover:text-accent line-clamp-2">
                                                                {{ post.title || $t('profile.no_title') }}
                                                            </h4>
                                                        </button>

                                                        <div v-if="isOwner" class="flex items-center gap-2 shrink-0">
                                                            <button
                                                                @click.stop="goTo(`/posts/${post.id}/edit`)"
                                                                class="w-10 h-10 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-300 hover:text-accent hover:border-accent/40 hover:bg-accent/10 transition-colors"
                                                                :title="$t('common.edit')"
                                                            >
                                                                <i class="fa-solid fa-pen"></i>
                                                            </button>
                                                            <button
                                                                @click.stop="confirmDelete(post)"
                                                                class="w-10 h-10 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-300 hover:text-red-500 hover:border-red-300 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors"
                                                                :title="$t('common.delete')"
                                                            >
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div class="flex items-center gap-2 flex-wrap mt-2">
                                                        <span
                                                            v-for="tag in post.tags"
                                                            :key="tag.id"
                                                            class="text-[11px] font-black tracking-wider uppercase px-2.5 py-0.5 rounded-full"
                                                            :style="{ backgroundColor: withAlpha(tag.color || '#F5B800', 0.16), color: tag.color || '#F5B800' }"
                                                        >
                                                            <i :class="tag.icon || 'fa-solid fa-tag'" class="mr-1"></i>
                                                            {{ tag.name }}
                                                        </span>
                                                        <span class="text-xs text-gray-400 dark:text-gray-500">
                                                            {{ formatRelativeTime(post.created_at) }}
                                                        </span>
                                                    </div>

                                                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed mt-3 line-clamp-2">
                                                        {{ getDescriptionPreview(post.description) }}
                                                    </p>

                                                    <div class="flex items-center gap-4 mt-3 text-xs font-bold text-gray-400 dark:text-gray-500">
                                                        <span class="inline-flex items-center gap-1.5">
                                                            <i class="fa-solid fa-arrow-up"></i>
                                                            {{ post.likes_count || 0 }} {{ $t('profile.votes') }}
                                                        </span>
                                                        <span class="inline-flex items-center gap-1.5">
                                                            <i class="fa-solid fa-comment"></i>
                                                            {{ post.comments_count || 0 }} {{ $t('profile.comments') }}
                                                        </span>
                                                        <span class="inline-flex items-center gap-1.5">
                                                            <i class="fa-solid fa-fire"></i>
                                                            {{ hotScore(post).toFixed(1) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </article>
                                    </div>
                                </section>
                            </div>

                            <div v-else class="px-5 py-16 text-center">
                                <div class="w-14 h-14 mx-auto rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 mb-4">
                                    <i class="fa-solid fa-file-lines text-xl"></i>
                                </div>
                                <div class="font-extrabold text-gray-900 dark:text-white">{{ $t('profile.no_posts_title') }}</div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ search ? $t('profile.no_results') : $t('profile.no_posts') }}</p>
                            </div>
                        </section>
                    </div>
                </section>
            </main>
        </div>

        <CreatePostModal v-if="showCreatePostModal" @close="showCreatePostModal = false" @success="refreshProfile" />
    </div>
</template>

<script setup>
import Header from '@/Components/Header.vue';
import FloatingSidebarToggle from '@/Components/FloatingSidebarToggle.vue';
import CreatePostModal from './CreatePostModal.vue';
import axios from 'axios';
import moment from 'moment';
import 'moment/dist/locale/pt-br';
import Swal from 'sweetalert2';
import { computed, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const props = defineProps({
    user: Object,
    posts: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
    isOwner: {
        type: Boolean,
        default: false,
    },
});

const { t, locale } = useI18n();
const page = usePage();

const search = ref('');
const activeFilter = ref('all');
const sortBy = ref('hot');
const sortDirection = ref('desc');
const showCreatePostModal = ref(false);
const isSidebarCollapsed = ref(false);
const localPosts = ref(props.posts.map(post => ({ ...post })));
const forumNameInput = ref(page.props.forumName || 'TechDevs');
const savingSettings = ref(false);
const settingsSaved = ref(false);

const saveSettings = async () => {
    savingSettings.value = true;
    settingsSaved.value = false;
    try {
        await axios.put('/admin/settings', { forum_name: forumNameInput.value });
        settingsSaved.value = true;
        setTimeout(() => { settingsSaved.value = false; }, 3000);
    } catch (error) {
        const message = error.response?.data?.message || t('common.error');

        Swal.fire({
            icon: 'error',
            title: t('common.error'),
            text: message,
            background: isDarkMode() ? '#111827' : '#ffffff',
            color: isDarkMode() ? '#f9fafb' : '#111827',
        });
    }
    savingSettings.value = false;
};

watch(
    () => props.posts,
    (posts) => {
        localPosts.value = posts.map(post => ({ ...post }));
    },
);

const navItems = computed(() => [
    ...(page.props.auth.user?.is_admin ? [{ id: 'dashboard', label: t('forum.dashboard'), href: '/dashboard', icon: 'fa-solid fa-table-cells-large', active: false }] : []),
    { id: 'forum', label: t('forum.forum'), href: '/forum', icon: 'fa-solid fa-comments', active: false },
    ...(page.props.auth.user?.is_admin ? [{ id: 'tags', label: t('tags.manage_tags'), href: '/forum/tags', icon: 'fa-solid fa-tags', active: false }] : []),
    { id: 'meus', label: t('forum.my_posts'), href: `/users/${page.props.auth.user.id}`, icon: 'fa-solid fa-user', active: props.isOwner },
]);

const metrics = computed(() => [
    { id: 'posts', label: t('profile.total_posts'), value: props.stats.posts_count ?? localPosts.value.length },
    { id: 'votes', label: t('profile.total_votes'), value: totalVotes.value },
    { id: 'comments', label: t('profile.total_comments'), value: totalComments.value },
    { id: 'hot', label: t('profile.hot_score'), value: topHotScore.value.toFixed(1) },
]);

const totalVotes = computed(() => localPosts.value.reduce((sum, post) => sum + (post.likes_count || 0), 0));
const totalComments = computed(() => localPosts.value.reduce((sum, post) => sum + (post.comments_count || 0), 0));
const topHotScore = computed(() => Math.max(0, ...localPosts.value.map(post => hotScore(post))));

const quickFilters = computed(() => [
    { id: 'all', label: t('profile.all_posts'), icon: 'fa-solid fa-layer-group', count: localPosts.value.length },
    { id: 'commented', label: t('profile.with_comments'), icon: 'fa-solid fa-comments', count: localPosts.value.filter(post => (post.comments_count || 0) > 0).length },
    { id: 'silent', label: t('profile.without_comments'), icon: 'fa-regular fa-comment', count: localPosts.value.filter(post => !(post.comments_count > 0)).length },
    { id: 'voted', label: t('profile.voted_posts'), icon: 'fa-solid fa-arrow-up', count: localPosts.value.filter(post => (post.likes_count || 0) > 0).length },
]);

const filteredPosts = computed(() => {
    const term = search.value.trim().toLowerCase();

    return localPosts.value
        .filter((post) => {
            if (activeFilter.value === 'commented' && !(post.comments_count > 0)) return false;
            if (activeFilter.value === 'silent' && (post.comments_count > 0)) return false;
            if (activeFilter.value === 'voted' && !(post.likes_count > 0)) return false;
            if (!term) return true;

            return [
                post.title,
                getDescriptionPreview(post.description),
                ...(post.tags || []).map(tag => tag.name),
            ]
                .filter(Boolean)
                .some(value => String(value).toLowerCase().includes(term));
        })
        .sort((a, b) => comparePosts(a, b));
});

const timelineGroups = computed(() => {
    const groups = [
        { id: 'today', label: t('profile.timeline_today'), icon: 'fa-solid fa-sun', posts: [] },
        { id: 'week', label: t('profile.timeline_week'), icon: 'fa-solid fa-calendar-week', posts: [] },
        { id: 'month', label: t('profile.timeline_month'), icon: 'fa-solid fa-calendar-days', posts: [] },
        { id: 'older', label: t('profile.timeline_older'), icon: 'fa-solid fa-box-archive', posts: [] },
    ];

    filteredPosts.value.forEach((post) => {
        const ageDays = moment().diff(moment(post.created_at), 'days');
        if (ageDays < 1) groups[0].posts.push(post);
        else if (ageDays < 7) groups[1].posts.push(post);
        else if (ageDays < 30) groups[2].posts.push(post);
        else groups[3].posts.push(post);
    });

    return groups.filter(group => group.posts.length);
});

const comparePosts = (a, b) => {
    const direction = sortDirection.value === 'asc' ? 1 : -1;

    if (sortBy.value === 'votes') {
        return ((a.likes_count || 0) - (b.likes_count || 0)) * direction;
    }

    if (sortBy.value === 'comments') {
        return ((a.comments_count || 0) - (b.comments_count || 0)) * direction;
    }

    if (sortBy.value === 'recent') {
        return (new Date(a.created_at).getTime() - new Date(b.created_at).getTime()) * direction;
    }

    return (hotScore(a) - hotScore(b)) * direction;
};

const hotScore = (post) => {
    const ageHours = Math.max(0, moment().diff(moment(post.created_at), 'hours'));
    const recencyBoost = Math.max(0, 72 - ageHours) / 24;

    return ((post.likes_count || 0) * 3) + ((post.comments_count || 0) * 1.5) + recencyBoost;
};

const toggleVote = async (post) => {
    const previousLiked = Boolean(post.liked_by_current_user);
    const previousCount = post.likes_count || 0;

    post.liked_by_current_user = !previousLiked;
    post.likes_count = Math.max(0, previousCount + (previousLiked ? -1 : 1));

    try {
        const response = await axios.post(`/posts/${post.id}/like`);
        post.liked_by_current_user = response.data.liked;
        post.likes_count = response.data.likes_count;
    } catch (error) {
        post.liked_by_current_user = previousLiked;
        post.likes_count = previousCount;

        Swal.fire({
            icon: 'error',
            title: t('common.error'),
            text: t('profile.vote_error'),
            background: isDarkMode() ? '#111827' : '#ffffff',
            color: isDarkMode() ? '#f9fafb' : '#111827',
        });
    }
};

const confirmDelete = async (post) => {
    const result = await Swal.fire({
        title: t('profile.delete_title'),
        text: t('profile.confirm_delete_named', { title: post.title }),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: t('common.yes_delete'),
        cancelButtonText: t('common.cancel'),
        reverseButtons: true,
        focusCancel: true,
        background: isDarkMode() ? '#111827' : '#ffffff',
        color: isDarkMode() ? '#f9fafb' : '#111827',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        customClass: {
            popup: 'profile-delete-alert',
            confirmButton: 'profile-delete-confirm',
            cancelButton: 'profile-delete-cancel',
        },
    });

    if (!result.isConfirmed) return;

    Swal.fire({
        title: t('common.loading'),
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        background: isDarkMode() ? '#111827' : '#ffffff',
        color: isDarkMode() ? '#f9fafb' : '#111827',
        didOpen: () => Swal.showLoading(),
    });

    try {
        await axios.delete(`/posts/${post.id}`);
        localPosts.value = localPosts.value.filter(item => item.id !== post.id);

        Swal.fire({
            icon: 'success',
            title: t('profile.deleted_title'),
            text: t('profile.deleted_message'),
            timer: 1600,
            showConfirmButton: false,
            background: isDarkMode() ? '#111827' : '#ffffff',
            color: isDarkMode() ? '#f9fafb' : '#111827',
        });
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: t('common.error'),
            text: error.response?.data?.message || t('profile.delete_error'),
            background: isDarkMode() ? '#111827' : '#ffffff',
            color: isDarkMode() ? '#f9fafb' : '#111827',
        });
    }
};

const refreshProfile = () => {
    showCreatePostModal.value = false;
    router.reload({ only: ['posts', 'stats'] });
};

const goTo = (href) => {
    router.get(href);
};

const formatRelativeTime = (date) => {
    return moment(date).locale(locale.value === 'pt-BR' ? 'pt-br' : 'en').fromNow();
};

const getDescriptionPreview = (description = '') => {
    const plainText = String(description).replace(/<[^>]+>/g, '').replace(/\s+/g, ' ').trim();
    return plainText.length > 180 ? `${plainText.slice(0, 180)}...` : plainText;
};

const withAlpha = (hex, alpha) => {
    const clean = String(hex || '#F5B800').replace('#', '');
    if (clean.length !== 6) return `rgba(245, 184, 0, ${alpha})`;

    const r = parseInt(clean.slice(0, 2), 16);
    const g = parseInt(clean.slice(2, 4), 16);
    const b = parseInt(clean.slice(4, 6), 16);

    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
};

const isDarkMode = () => document.documentElement.classList.contains('dark');
</script>

<style scoped>
.profile-page {
    color-scheme: light;
}

:global(.dark) .profile-page {
    color-scheme: dark;
}

:global(.profile-delete-alert) {
    border-radius: 1rem;
    border: 1px solid rgb(229 231 235);
}

:global(.dark .profile-delete-alert) {
    border-color: rgb(55 65 81);
}

:global(.profile-delete-confirm),
:global(.profile-delete-cancel) {
    border-radius: 0.75rem !important;
    padding: 0.7rem 1rem !important;
    font-weight: 800 !important;
}
</style>
