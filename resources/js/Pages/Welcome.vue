<template>
    <Head :title="$page.props.forumName || 'TechDevs'" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 flex flex-col">
        <!-- Header -->
        <PublicHeader
            :isAuthenticated="isAuthenticated"
            @open-auth="openAuth"
            @search="onSearch"
        />

        <!-- Hero Banner -->
        <section class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-6xl mx-auto px-6 py-10 text-center">
                <div class="flex items-center justify-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-accent flex items-center justify-center">
                        <svg width="24" height="24" viewBox="0 0 18 18" fill="none">
                            <path d="M2 4C2 2.9 2.9 2 4 2H14C15.1 2 16 2.9 16 4V11C16 12.1 15.1 13 14 13H10L6 16V13H4C2.9 13 2 12.1 2 11V4Z" fill="white"/>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                        {{ $page.props.forumName || 'TechDevs' }}<span class="text-accent">.</span>
                    </h1>
                </div>
                <p class="text-gray-500 dark:text-gray-400 text-base max-w-md mx-auto mb-6">
                    {{ $t('welcome.tagline') }}
                </p>
                <button v-if="!isAuthenticated" @click="openAuth('register')"
                        class="px-8 py-3 rounded-xl border-none bg-accent text-white font-bold text-sm cursor-pointer shadow-[0_4px_20px_rgba(245,184,0,0.35)] hover:-translate-y-0.5 hover:shadow-[0_6px_24px_rgba(245,184,0,0.4)] transition-all">
                    {{ $t('welcome.join_cta') }}
                </button>
                <a v-else href="/forum"
                   class="inline-block px-8 py-3 rounded-xl bg-accent text-white font-bold text-sm no-underline shadow-[0_4px_20px_rgba(245,184,0,0.35)] hover:-translate-y-0.5 transition-all">
                    {{ $t('forum.forum') }}
                </a>
            </div>
        </section>

        <!-- Main content -->
        <div class="flex-1 max-w-6xl mx-auto w-full px-6 py-8 flex gap-8">
            <!-- Left Sidebar -->
            <aside class="w-56 shrink-0 hidden lg:block">
                <!-- Categories -->
                <div class="bg-white dark:bg-gray-900 rounded-2xl p-4 mb-4">
                    <p class="text-[10.5px] font-extrabold tracking-[0.1em] text-gray-400 dark:text-gray-500 px-2 py-1.5 uppercase">
                        {{ $t('forum.categories') }}
                    </p>
                    <button v-for="cat in categories" :key="cat.id"
                            @click="filterPosts(cat.id)"
                            class="w-full flex items-center gap-2.5 px-3 py-2 rounded-[10px] border-none cursor-pointer text-[13px] mb-0.5 transition-all text-left"
                            :class="activeCat === cat.id
                                ? 'bg-accent/15 text-accent font-bold'
                                : 'bg-transparent text-gray-500 dark:text-gray-400 font-medium hover:bg-gray-100 dark:hover:bg-gray-800'">
                        <i v-if="isIconClass(cat.icon)" :class="cat.icon" class="text-[15px] w-[20px] text-center"></i>
                        <span v-else class="text-[15px] w-[20px] text-center">{{ cat.icon }}</span>
                        {{ cat.label }}
                        <span class="ml-auto text-[11px] font-bold px-1.5 py-0.5 rounded-full"
                              :class="activeCat === cat.id
                                  ? 'bg-accent/25 text-accent'
                                  : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400'">
                            {{ getCategoryCount(cat.id) }}
                        </span>
                    </button>
                </div>

                <!-- Sort -->
                <div class="bg-white dark:bg-gray-900 rounded-2xl p-4">
                    <p class="text-[10.5px] font-extrabold tracking-[0.1em] text-gray-400 dark:text-gray-500 px-2 py-1.5 uppercase">
                        {{ $t('forum.navigate') }}
                    </p>
                    <button v-for="s in sortOptions" :key="s.value"
                            @click="sortPosts(s.value)"
                            class="w-full flex items-center gap-2 px-3 py-2 rounded-[10px] border-none cursor-pointer text-[13px] mb-0.5 transition-all text-left"
                            :class="sortOption === s.value
                                ? 'bg-accent/15 text-accent font-bold'
                                : 'bg-transparent text-gray-500 dark:text-gray-400 font-medium hover:bg-gray-100 dark:hover:bg-gray-800'">
                        {{ s.label }}
                    </button>
                </div>
            </aside>

            <!-- Center: Post Feed -->
            <main class="flex-1 min-w-0">
                <!-- Mobile sort -->
                <div class="flex gap-1.5 bg-gray-200/70 dark:bg-gray-800 rounded-xl p-1 mb-4 lg:hidden overflow-x-auto">
                    <button v-for="s in sortOptions" :key="s.value"
                            @click="sortPosts(s.value)"
                            class="px-3.5 py-[7px] rounded-[9px] border-none cursor-pointer text-[13px] transition-all whitespace-nowrap"
                            :class="sortOption === s.value
                                ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-bold shadow-sm'
                                : 'bg-transparent text-gray-500 dark:text-gray-400 font-medium'">
                        {{ s.label }}
                    </button>
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
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $t('forum.be_first') }}</div>
                </div>

                <!-- Posts -->
                <div v-else class="flex flex-col gap-3">
                    <PostCard
                        v-for="post in loadedPosts"
                        :key="post.id"
                        :post="post"
                        :isAuthenticated="isAuthenticated"
                        @vote="toggleVote"
                        @request-auth="openAuth('login')"
                        @click="viewPost(post)"
                    />
                </div>

                <!-- Load more / infinite scroll sentinel -->
                <div ref="sentinel" class="h-4"></div>
                <div v-if="isLoading && loadedPosts.length" class="flex justify-center py-6">
                    <svg class="animate-spin h-8 w-8 text-accent" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </main>

            <!-- Right Sidebar -->
            <aside class="w-60 shrink-0 hidden xl:block">
                <!-- Trending Tags -->
                <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 mb-4">
                    <h3 class="text-xs font-extrabold tracking-wider text-gray-400 dark:text-gray-500 uppercase mb-3">
                        {{ $t('welcome.trending_tags') }}
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        <button v-for="tag in tags" :key="tag.id"
                                @click="filterPosts(String(tag.id))"
                                class="px-3 py-1.5 rounded-full text-xs font-semibold border-none cursor-pointer transition-colors"
                                :style="{ background: tag.color + '20', color: tag.color }">
                            {{ tag.name }}
                        </button>
                    </div>
                </div>

                <!-- Stats -->
                <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 mb-4">
                    <h3 class="text-xs font-extrabold tracking-wider text-gray-400 dark:text-gray-500 uppercase mb-3">
                        {{ $t('welcome.community_stats') }}
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $t('welcome.members_count') }}</span>
                            <span class="text-sm font-bold text-gray-900 dark:text-white">{{ stats.members || 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $t('welcome.posts_count') }}</span>
                            <span class="text-sm font-bold text-gray-900 dark:text-white">{{ stats.posts || 0 }}</span>
                        </div>
                    </div>
                </div>

                <!-- Join CTA (guest only) -->
                <div v-if="!isAuthenticated" class="bg-gradient-to-br from-accent/10 to-accent/5 dark:from-accent/20 dark:to-accent/5 rounded-2xl p-5 border border-accent/20">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-2">
                        {{ $t('welcome.join_community') }}
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4 leading-relaxed">
                        {{ $t('welcome.join_description') }}
                    </p>
                    <button @click="openAuth('register')"
                            class="w-full py-2.5 rounded-xl border-none bg-accent text-white font-bold text-sm cursor-pointer shadow-[0_4px_16px_rgba(245,184,0,0.35)] hover:bg-accent-dark transition-colors">
                        {{ $t('auth.register') }}
                    </button>
                </div>
            </aside>
        </div>

        <!-- Auth Modal -->
        <AuthModal :show="showAuthModal" :initialTab="authTab" @close="closeAuth" />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import PublicHeader from '@/Components/PublicHeader.vue';
import PostCard from '@/Components/PostCard.vue';
import AuthModal from '@/Components/AuthModal.vue';
import axios from 'axios';

const { t } = useI18n();
const page = usePage();

const props = defineProps({
    posts: Object,
    filters: Object,
    tags: Array,
    categoryCounts: Object,
    isAuthenticated: { type: Boolean, default: false },
    stats: { type: Object, default: () => ({ members: 0, posts: 0 }) },
});

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
    { value: 'most_voted', label: t('forum.most_voted') },
]);

const activeCat = ref(props.filters?.tag || 'Todos');
const sortOption = ref(props.filters?.sort || 'latest');
const currentPage = ref(1);
const isLoading = ref(false);
const loadedPosts = ref(props.posts?.data || []);
const totalPosts = ref(props.posts?.total || 0);
const searchTerm = ref('');
const sentinel = ref(null);
let observer = null;

const showAuthModal = ref(false);
const authTab = ref('login');
const authTabs = ['login', 'register'];

const hasMorePosts = computed(() => loadedPosts.value.length < totalPosts.value);

const getCategoryCount = (catId) => {
    if (props.categoryCounts) {
        return props.categoryCounts[String(catId)] ?? 0;
    }
    return 0;
};

const isIconClass = (icon) => String(icon || '').includes('fa-');

const openAuth = (tab = 'login') => {
    authTab.value = authTabs.includes(tab) ? tab : 'login';
    showAuthModal.value = true;
};

const closeAuth = () => {
    showAuthModal.value = false;
    sessionStorage.setItem('auth_modal_shown', 'true');
    clearAuthQuery();
};

const getAuthTabFromUrl = () => {
    const tab = new URLSearchParams(window.location.search).get('auth');
    return authTabs.includes(tab) ? tab : null;
};

const clearAuthQuery = () => {
    const url = new URL(window.location.href);

    if (!url.searchParams.has('auth')) {
        return;
    }

    url.searchParams.delete('auth');
    const nextUrl = `${url.pathname}${url.search}${url.hash}`;
    window.history.replaceState({}, '', nextUrl);
};

const onSearch = (query) => {
    searchTerm.value = query;
};

const viewPost = (post) => {
    router.get(`/posts/${post.id}`);
};

const fetchPosts = (reset = false) => {
    if (reset) {
        currentPage.value = 1;
        loadedPosts.value = [];
    }

    isLoading.value = true;
    router.get('/', {
        tag: activeCat.value,
        sort: sortOption.value,
        page: currentPage.value,
        per_page: 10,
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
        },
        onError: () => { isLoading.value = false; },
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
    if (!hasMorePosts.value || isLoading.value) return;
    currentPage.value += 1;
    fetchPosts();
};

const toggleVote = async (post) => {
    if (!props.isAuthenticated) {
        openAuth('login');
        return;
    }
    const previousLiked = Boolean(post.liked_by_current_user);
    const previousCount = post.likes_count || 0;
    post.liked_by_current_user = !previousLiked;
    post.likes_count = Math.max(0, previousCount + (previousLiked ? -1 : 1));
    try {
        const response = await axios.post(`/posts/${post.id}/like`);
        post.liked_by_current_user = response.data.liked;
        post.likes_count = response.data.likes_count;
    } catch {
        post.liked_by_current_user = previousLiked;
        post.likes_count = previousCount;
    }
};

onMounted(() => {
    const requestedAuthTab = getAuthTabFromUrl();

    if (props.isAuthenticated) {
        clearAuthQuery();
    } else if (requestedAuthTab) {
        openAuth(requestedAuthTab);
        sessionStorage.setItem('auth_modal_shown', 'true');
    }

    // Show auth modal once per session for guests
    if (!props.isAuthenticated && !requestedAuthTab && !sessionStorage.getItem('auth_modal_shown')) {
        setTimeout(() => {
            showAuthModal.value = true;
        }, 2000);
    }

    // Infinite scroll
    if (sentinel.value) {
        observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && hasMorePosts.value && !isLoading.value) {
                loadMorePosts();
            }
        }, { rootMargin: '200px' });
        observer.observe(sentinel.value);
    }
});

onBeforeUnmount(() => {
    if (observer) {
        observer.disconnect();
    }
});
</script>
