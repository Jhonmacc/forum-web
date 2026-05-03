<template>
    <div class="relative flex h-screen overflow-hidden bg-gray-50 dark:bg-gray-950">
        <FloatingSidebarToggle v-model:open="isSidebarOpen" />

        <!-- Sidebar -->
        <aside
            class="shrink-0 overflow-hidden bg-white transition-all duration-300 ease-in-out dark:bg-gray-900"
            :class="isSidebarOpen ? 'w-[248px] border-r border-gray-200 dark:border-gray-700' : 'w-0 border-r-0 pointer-events-none'"
            :aria-hidden="!isSidebarOpen"
        >
            <div class="flex h-full w-[248px] flex-col overflow-hidden transition-opacity duration-200" :class="isSidebarOpen ? 'opacity-100' : 'opacity-0'">
            <!-- Logo -->
            <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2.5">
                    <div class="w-[34px] h-[34px] rounded-[10px] bg-accent flex items-center justify-center">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                            <path d="M2 4C2 2.9 2.9 2 4 2H14C15.1 2 16 2.9 16 4V11C16 12.1 15.1 13 14 13H10L6 16V13H4C2.9 13 2 12.1 2 11V4Z" fill="white"/>
                        </svg>
                    </div>
                    <span class="font-extrabold text-base text-gray-900 dark:text-white tracking-tight">
                        {{ $page.props.forumName || 'TechDevs' }}<span class="text-accent">.</span>
                    </span>
                </div>
            </div>

            <!-- New Post Button -->
            <div class="px-4 pt-5 pb-4">
                <button @click="openCreatePostModal"
                        class="w-full py-3 rounded-xl border-none bg-accent text-white font-bold text-sm flex items-center justify-center gap-2 shadow-[0_4px_20px_rgba(245,184,0,0.3)] hover:-translate-y-0.5 hover:shadow-[0_6px_24px_rgba(245,184,0,0.4)] transition-all">
                    <span class="text-lg leading-none">+</span> {{ $t('forum.new_discussion') }}
                </button>
            </div>

            <!-- Nav -->
            <nav class="flex-1 overflow-auto px-2.5">
                <p class="text-[10.5px] font-extrabold tracking-[0.1em] text-gray-400 dark:text-gray-500 px-3 py-2 uppercase">{{ $t('forum.navigate') }}</p>
                <button v-for="item in navItems" :key="item.id"
                        @click="setActiveTab(item.id)"
                        class="w-full flex items-center gap-2.5 px-3.5 py-2.5 rounded-[10px] border-none cursor-pointer text-sm mb-0.5 transition-all text-left"
                        :class="activeTab === item.id
                            ? 'bg-accent/15 text-accent font-bold'
                            : 'bg-transparent text-gray-500 dark:text-gray-400 font-medium hover:bg-gray-100 dark:hover:bg-gray-800'">
                    <component :is="item.icon" :active="activeTab === item.id" />
                    {{ item.label }}
                </button>

                <div class="h-px bg-gray-200 dark:bg-gray-700 mx-3 my-4"></div>

                <p class="text-[10.5px] font-extrabold tracking-[0.1em] text-gray-400 dark:text-gray-500 px-3 py-2 uppercase">{{ $t('forum.categories') }}</p>
                <button v-for="cat in categories" :key="cat.id"
                        @click="filterPosts(cat.id)"
                        class="w-full flex items-center gap-2.5 px-3.5 py-2 rounded-[10px] border-none cursor-pointer text-[13.5px] mb-0.5 transition-all text-left"
                        :class="activeCat === cat.id
                            ? 'bg-accent/15 text-accent font-bold'
                            : 'bg-transparent text-gray-500 dark:text-gray-400 font-medium hover:bg-gray-100 dark:hover:bg-gray-800'">
                    <i v-if="isIconClass(cat.icon)" :class="cat.icon" class="text-[15px] w-[22px] text-center"></i>
                    <span v-else class="text-[15px] w-[22px] text-center">{{ cat.icon }}</span>
                    {{ cat.label }}
                    <span class="ml-auto text-[11px] font-bold px-1.5 py-0.5 rounded-full"
                          :class="activeCat === cat.id
                              ? 'bg-accent/25 text-accent'
                              : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400'">
                        {{ getCategoryCount(cat.id) }}
                    </span>
                </button>
            </nav>

            <!-- User -->
            <div class="px-4 py-3.5 border-t border-gray-200 dark:border-gray-700 flex items-center gap-2.5">
                <div class="w-[34px] h-[34px] rounded-full overflow-hidden shrink-0">
                    <img class="w-full h-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-[13px] font-bold text-gray-900 dark:text-white truncate">{{ $page.props.auth.user.name }}</div>
                </div>
                <a :href="route('profile.show')" class="text-gray-400 hover:text-gray-700 dark:hover:text-white transition-colors text-lg">⚙</a>
            </div>
            </div>
        </aside>

        <!-- Main -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <Header @search="onSearch" />

            <!-- Content -->
            <main class="flex-1 overflow-auto p-7">
                <!-- Page header -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-[22px] font-extrabold tracking-tight mb-0.5 text-gray-900 dark:text-white">
                            {{ currentCategoryLabel }}
                        </h1>
                        <p class="text-[13px] text-gray-500 dark:text-gray-400">
                            {{ filteredPostsCount === 1 ? $t('forum.discussions_one', { count: filteredPostsCount }) : $t('forum.discussions_many', { count: filteredPostsCount }) }}{{ searchTerm ? ' ' + $t('forum.for_search', { term: searchTerm }) : '' }}
                        </p>
                    </div>

                    <!-- Sort tabs -->
                    <div class="flex gap-1.5 bg-gray-200/70 dark:bg-gray-800 rounded-xl p-1">
                        <button v-for="s in sortOptions" :key="s.value"
                                @click="sortPosts(s.value)"
                                class="px-3.5 py-[7px] rounded-[9px] border-none cursor-pointer text-[13px] transition-all"
                                :class="sortOption === s.value
                                    ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-bold shadow-sm'
                                    : 'bg-transparent text-gray-500 dark:text-gray-400 font-medium hover:text-gray-700 dark:hover:text-gray-200'">
                            {{ s.label }}
                        </button>
                    </div>
                </div>

                <!-- Loading -->
                <div v-if="isLoading && !loadedPosts.length" class="flex justify-center items-center h-64">
                    <svg class="animate-spin h-10 w-10 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <!-- Empty state -->
                <div v-else-if="!loadedPosts.length && !isLoading" class="text-center py-20">
                    <div class="text-5xl mb-4">💬</div>
                    <div class="font-bold text-lg mb-2 text-gray-900 dark:text-white">{{ $t('forum.no_discussion_found') }}</div>
                    <div class="text-sm mb-6 text-gray-500 dark:text-gray-400">
                        {{ searchTerm ? $t('forum.no_results_for', { term: searchTerm }) : $t('forum.be_first') }}
                    </div>
                    <button @click="openCreatePostModal"
                            class="px-7 py-3 rounded-xl border-none bg-accent text-white font-bold text-sm cursor-pointer shadow-[0_4px_16px_rgba(245,184,0,0.35)]">
                        + {{ $t('forum.new_discussion') }}
                    </button>
                </div>

                <!-- Posts -->
                <transition name="fade" mode="out-in">
                    <div v-if="loadedPosts.length" key="posts" class="flex flex-col gap-3">
                        <PostCard
                            v-for="post in loadedPosts"
                            :key="post.id"
                            :post="post"
                            :isAuthenticated="true"
                            @vote="toggleVote"
                            @click="router.get(`/posts/${post.id}/edit`)"
                        />
                    </div>
                </transition>

                <!-- Load more -->
                <div v-if="hasMorePosts" class="flex justify-center mt-6">
                    <button @click="loadMorePosts"
                            class="flex items-center gap-2 px-6 py-2.5 bg-accent text-white rounded-xl font-bold text-sm hover:bg-accent-dark transition-colors shadow-[0_4px_16px_rgba(245,184,0,0.35)]">
                        <span>{{ $t('forum.load_more') }}</span>
                        <i v-if="isLoading" class="fa-solid fa-spinner fa-spin"></i>
                    </button>
                </div>
            </main>
        </div>

        <!-- Create Post Modal -->
        <CreatePostModal v-if="showCreatePostModal" @close="closeCreatePostModal" @success="refreshPosts" />
    </div>
</template>

<script setup>
import { router, usePage } from '@inertiajs/vue3';
import Header from '@/Components/Header.vue';
import PostCard from '@/Components/PostCard.vue';
import FloatingSidebarToggle from '@/Components/FloatingSidebarToggle.vue';
import CreatePostModal from './CreatePostModal.vue';
import axios from 'axios';
import moment from 'moment';
import 'moment/dist/locale/pt-br';
import { ref, computed, h } from 'vue';
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n();
const page = usePage();

const props = defineProps({
    posts: Object,
    filters: Object,
    tags: Array,
    categoryCounts: Object,
});

const DashIcon = {
    props: ['active'],
    render() {
        const c = this.active ? '#F5B800' : 'currentColor';
        return h('svg', { width: 16, height: 16, viewBox: '0 0 16 16', fill: 'none', class: this.active ? '' : 'text-gray-400 dark:text-gray-500' }, [
            h('rect', { x: 1, y: 1, width: 6, height: 6, rx: 2, fill: c }),
            h('rect', { x: 9, y: 1, width: 6, height: 6, rx: 2, fill: c, 'fill-opacity': '0.5' }),
            h('rect', { x: 1, y: 9, width: 6, height: 6, rx: 2, fill: c, 'fill-opacity': '0.5' }),
            h('rect', { x: 9, y: 9, width: 6, height: 6, rx: 2, fill: c }),
        ]);
    }
};
const ForumIcon = {
    props: ['active'],
    render() {
        const c = this.active ? '#F5B800' : 'currentColor';
        return h('svg', { width: 16, height: 16, viewBox: '0 0 16 16', fill: 'none', class: this.active ? '' : 'text-gray-400 dark:text-gray-500' }, [
            h('path', { d: 'M1 3C1 2.4 1.4 2 2 2H11C11.6 2 12 2.4 12 3V8C12 8.6 11.6 9 11 9H7L4 12V9H2C1.4 9 1 8.6 1 8V3Z', fill: c }),
            h('path', { d: 'M13 5H14C14.6 5 15 5.4 15 6V10C15 10.6 14.6 11 14 11H13V13L10 11H8', stroke: c, 'stroke-width': '1.4', 'stroke-linecap': 'round', 'stroke-linejoin': 'round' }),
        ]);
    }
};
const UserIcon = {
    props: ['active'],
    render() {
        const c = this.active ? '#F5B800' : 'currentColor';
        return h('svg', { width: 16, height: 16, viewBox: '0 0 16 16', fill: 'none', class: this.active ? '' : 'text-gray-400 dark:text-gray-500' }, [
            h('circle', { cx: 8, cy: 5, r: 3, fill: c }),
            h('path', { d: 'M2 14C2 11.2 4.7 9 8 9C11.3 9 14 11.2 14 14', stroke: c, 'stroke-width': '1.5', 'stroke-linecap': 'round' }),
        ]);
    }
};

const navItems = computed(() => [
    ...(page.props.auth.user?.is_admin ? [{ id: 'dashboard', label: t('forum.dashboard'), icon: DashIcon }] : []),
    { id: 'forum', label: t('forum.forum'), icon: ForumIcon },
    ...(page.props.auth.user?.is_admin ? [{ id: 'tags', label: t('tags.manage_tags'), icon: ForumIcon }] : []),
    { id: 'meus', label: t('forum.my_posts'), icon: UserIcon },
]);

const categories = computed(() => [
    { id: 'Todos', label: t('forum.all_discussions'), icon: 'fa-solid fa-table-cells-large' },
    ...(props.tags || []).map(tag => ({
        id: String(tag.id),
        label: tag.name,
        icon: tag.icon || 'fa-solid fa-tag',
    })),
]);

const sortOptions = computed(() => [
    { value: 'hot', label: t('forum.hot') },
    { value: 'latest', label: t('forum.recent') },
    { value: 'newest', label: t('forum.popular') },
    { value: 'oldest', label: t('forum.no_reply') },
    { value: 'most_voted', label: t('forum.most_voted') },
]);

const AVATAR_HUES = ['#4a90d9', '#3dab5e', '#d4a028', '#8b5ec8', '#c84d7a', '#d05a3a'];

const showCreatePostModal = ref(false);
const isSidebarOpen = ref(true);
const activeTab = ref('forum');
const activeCat = ref(props.filters?.tag || 'Todos');
const sortOption = ref(props.filters?.sort || 'latest');
const currentPage = ref(1);
const perPage = ref(5);
const isLoading = ref(false);
const isRefreshing = ref(false);
const loadedPosts = ref(props.posts?.data || []);
const totalPosts = ref(props.posts?.total || 0);
const searchTerm = ref('');

const hasMorePosts = computed(() => loadedPosts.value.length < totalPosts.value);
const filteredPostsCount = computed(() => loadedPosts.value.length);
const currentCategoryLabel = computed(() => {
    const cat = categories.value.find(c => c.id === activeCat.value);
    return cat ? cat.label : t('forum.all_discussions');
});

const getCategoryCount = (catId) => {
    if (props.categoryCounts) {
        return props.categoryCounts[String(catId)] ?? 0;
    }
    if (catId === 'Todos') return totalPosts.value;
    return 0;
};

const getAvatarColor = (id) => AVATAR_HUES[(id || 0) % AVATAR_HUES.length];
const isIconClass = (icon) => String(icon || '').includes('fa-');

const getInitials = (name) => {
    if (!name) return '?';
    const parts = name.split(' ');
    return parts.length >= 2
        ? (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
        : name.slice(0, 2).toUpperCase();
};

const setActiveTab = (tab) => {
    activeTab.value = tab;
    if (tab === 'dashboard') {
        router.get('/dashboard');
    } else if (tab === 'forum') {
        router.get('/forum');
    } else if (tab === 'tags') {
        router.get('/forum/tags');
    } else if (tab === 'meus') {
        const userId = page.props.auth.user.id;
        router.visit(`/users/${userId}`);
    }
};

const fetchPosts = (reset = false) => {
    if (reset) {
        currentPage.value = 1;
        loadedPosts.value = [];
    }

    isLoading.value = true;
    router.get('/forum', {
        tag: activeCat.value,
        sort: sortOption.value,
        page: currentPage.value,
        per_page: perPage.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            const newPosts = page.props.posts.data;
            totalPosts.value = page.props.posts.total;
            if (reset) {
                loadedPosts.value = newPosts;
            } else {
                loadedPosts.value = [...loadedPosts.value, ...newPosts];
            }
            isLoading.value = false;
            isRefreshing.value = false;
        },
        onError: () => {
            isLoading.value = false;
            isRefreshing.value = false;
        },
        onFinish: () => {
            isLoading.value = false;
            isRefreshing.value = false;
        },
    });
};

const filterPosts = (tag) => {
    activeCat.value = tag;
    fetchPosts(true);
};

const sortPosts = (option) => {
    sortOption.value = option;
    fetchPosts(true);
};

const loadMorePosts = () => {
    currentPage.value += 1;
    fetchPosts();
};

const onSearch = (query) => {
    searchTerm.value = query;
};

const openCreatePostModal = () => {
    showCreatePostModal.value = true;
};

const closeCreatePostModal = () => {
    showCreatePostModal.value = false;
};

const refreshPosts = () => {
    router.get('/forum', {
        tag: activeCat.value,
        sort: sortOption.value,
    }, {
        preserveState: false,
        preserveScroll: true,
        onSuccess: (page) => {
            loadedPosts.value = page.props.posts.data;
            totalPosts.value = page.props.posts.total;
            currentPage.value = 1;
        },
    });
};

const formatRelativeTime = (date) => {
    return moment(date).locale(locale.value === 'pt-BR' ? 'pt-br' : 'en').fromNow();
};

const getDescriptionPreview = (description) => {
    const plainText = description.replace(/<[^>]+>/g, '');
    return plainText.length > 150 ? plainText.slice(0, 150) + '...' : plainText;
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
    }
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>
