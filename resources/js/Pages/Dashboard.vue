<template>
    <Head :title="$t('dashboard.page_title')" />

    <div class="relative flex h-screen overflow-hidden bg-gray-50 text-gray-900 dark:bg-gray-950 dark:text-gray-100">
        <FloatingSidebarToggle v-model:open="isSidebarOpen" class="hidden lg:flex" />

        <aside
            class="hidden shrink-0 overflow-hidden bg-white transition-all duration-300 ease-in-out dark:bg-gray-900 lg:flex"
            :class="isSidebarOpen ? 'w-[248px] border-r border-gray-200 dark:border-gray-700' : 'w-0 border-r-0 pointer-events-none'"
            :aria-hidden="!isSidebarOpen"
        >
            <div class="flex h-full w-[248px] flex-col overflow-hidden transition-opacity duration-200" :class="isSidebarOpen ? 'opacity-100' : 'opacity-0'">
            <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                <a href="/forum" class="flex items-center gap-2.5 no-underline">
                    <div class="w-[34px] h-[34px] rounded-[10px] bg-accent flex items-center justify-center">
                        <i class="fa-solid fa-comments text-white text-sm"></i>
                    </div>
                    <span class="font-extrabold text-base text-gray-900 dark:text-white tracking-tight">
                        {{ $page.props.forumName || 'TechDevs' }}<span class="text-accent">.</span>
                    </span>
                </a>
            </div>

            <div class="px-4 pt-5 pb-4">
                <a
                    href="/forum"
                    class="w-full py-3 rounded-xl bg-accent text-white font-bold text-sm flex items-center justify-center gap-2 shadow-[0_4px_20px_rgba(245,184,0,0.3)] hover:-translate-y-0.5 hover:shadow-[0_6px_24px_rgba(245,184,0,0.4)] transition-all no-underline"
                >
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    {{ $t('dashboard.back_to_forum') }}
                </a>
            </div>

            <nav class="flex-1 overflow-auto px-2.5">
                <p class="text-[10.5px] font-extrabold tracking-[0.1em] text-gray-400 dark:text-gray-500 px-3 py-2 uppercase">
                    {{ $t('dashboard.administration') }}
                </p>
                <a
                    v-for="item in navItems"
                    :key="item.label"
                    :href="item.href"
                    class="w-full flex items-center gap-2.5 px-3.5 py-2.5 rounded-[10px] text-sm mb-0.5 transition-all text-left no-underline"
                    :class="item.active
                        ? 'bg-accent/15 text-accent font-bold'
                        : 'bg-transparent text-gray-500 dark:text-gray-400 font-medium hover:bg-gray-100 dark:hover:bg-gray-800'"
                >
                    <i :class="item.icon" class="w-4 text-center"></i>
                    {{ item.label }}
                </a>

                <div class="h-px bg-gray-200 dark:bg-gray-700 mx-3 my-4"></div>

                <p class="text-[10.5px] font-extrabold tracking-[0.1em] text-gray-400 dark:text-gray-500 px-3 py-2 uppercase">
                    {{ $t('dashboard.indicators') }}
                </p>
                <div class="px-3 space-y-2">
                    <div
                        v-for="signal in signals"
                        :key="signal.label"
                        class="rounded-xl bg-gray-50 dark:bg-gray-950 px-3 py-2"
                    >
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-gray-500 dark:text-gray-400">{{ signal.label }}</span>
                            <span class="font-extrabold text-accent">{{ signal.value }}</span>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="px-4 py-3.5 border-t border-gray-200 dark:border-gray-700 flex items-center gap-2.5">
                <div class="w-[34px] h-[34px] rounded-full overflow-hidden shrink-0">
                    <img class="w-full h-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-[13px] font-bold text-gray-900 dark:text-white truncate">{{ $page.props.auth.user.name }}</div>
                </div>
                <a :href="route('profile.show')" class="text-gray-400 hover:text-gray-700 dark:hover:text-white transition-colors text-base">
                    <i class="fa-solid fa-gear"></i>
                </a>
            </div>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <Header />

            <main class="flex-1 overflow-auto p-5 md:p-7">
                <section class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between mb-6">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-accent/15 px-3 py-1 text-xs font-bold text-accent mb-3">
                            <i class="fa-solid fa-chart-line"></i>
                            {{ $t('dashboard.admin_panel') }}
                        </div>
                        <h1 class="text-[28px] font-extrabold tracking-tight text-gray-900 dark:text-white">
                            {{ $t('dashboard.title') }}
                        </h1>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                            {{ $t('dashboard.subtitle') }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a
                            href="/forum/tags"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border-[1.5px] border-gray-200 dark:border-gray-700 text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-900 transition-colors no-underline"
                        >
                            <i class="fa-solid fa-tags text-xs"></i>
                            {{ $t('dashboard.manage_tags') }}
                        </a>
                        <a
                            href="/forum"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-accent text-white text-sm font-bold shadow-[0_4px_16px_rgba(245,184,0,0.35)] hover:bg-accent-dark transition-colors no-underline"
                        >
                            <i class="fa-solid fa-comments text-xs"></i>
                            {{ $t('dashboard.open_forum') }}
                        </a>
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4 mb-6">
                    <article
                        v-for="metric in metrics"
                        :key="metric.label"
                        class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5 shadow-sm transition-all hover:-translate-y-0.5 hover:border-accent/50 hover:shadow-lg"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                    {{ metric.label }}
                                </p>
                                <div class="mt-2 text-3xl font-extrabold text-gray-900 dark:text-white">
                                    {{ metric.value }}
                                </div>
                            </div>
                            <div class="h-11 w-11 rounded-2xl flex items-center justify-center" :class="metric.iconBg">
                                <i :class="[metric.icon, metric.iconColor]"></i>
                            </div>
                        </div>
                        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                            {{ metric.caption }}
                        </p>
                    </article>
                </section>

                <section class="grid grid-cols-1 gap-6 2xl:grid-cols-[minmax(0,1.5fr)_minmax(360px,0.8fr)]">
                    <div class="space-y-6">
                        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 overflow-hidden">
                            <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                                <div>
                                    <h2 class="text-base font-extrabold text-gray-900 dark:text-white">{{ $t('dashboard.growth_title') }}</h2>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $t('dashboard.growth_subtitle') }}</p>
                                </div>
                                <div class="hidden md:flex items-center gap-3 text-xs font-bold text-gray-500 dark:text-gray-400">
                                    <span><i class="fa-solid fa-square text-accent mr-1"></i>{{ $t('dashboard.posts') }}</span>
                                    <span><i class="fa-solid fa-square text-emerald-500 mr-1"></i>{{ $t('dashboard.members') }}</span>
                                    <span><i class="fa-solid fa-square text-sky-500 mr-1"></i>{{ $t('dashboard.comments') }}</span>
                                </div>
                            </div>

                            <div class="p-5">
                                <div class="flex h-64 items-end gap-3">
                                    <div
                                        v-for="day in growth"
                                        :key="day.label"
                                        class="flex-1 h-full flex flex-col justify-end gap-2"
                                    >
                                        <div class="flex flex-1 items-end gap-1.5 rounded-xl bg-gray-50 dark:bg-gray-950 px-2 pb-3">
                                            <div class="flex-1 rounded-t-lg bg-accent min-h-[6px]" :style="{ height: barHeight(day.posts) }"></div>
                                            <div class="flex-1 rounded-t-lg bg-emerald-500 min-h-[6px]" :style="{ height: barHeight(day.members) }"></div>
                                            <div class="flex-1 rounded-t-lg bg-sky-500 min-h-[6px]" :style="{ height: barHeight(day.comments) }"></div>
                                        </div>
                                        <span class="text-center text-xs font-bold text-gray-400">{{ day.label }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 overflow-hidden">
                            <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                                <div>
                                    <h2 class="text-base font-extrabold text-gray-900 dark:text-white">{{ $t('dashboard.hot_posts_title') }}</h2>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $t('dashboard.hot_posts_subtitle') }}</p>
                                </div>
                                <span class="rounded-full bg-accent/15 px-3 py-1 text-xs font-bold text-accent">Hot</span>
                            </div>

                            <div class="divide-y divide-gray-200 dark:divide-gray-800">
                                <a
                                    v-for="post in topPosts"
                                    :key="post.id"
                                    :href="`/posts/${post.id}/edit`"
                                    class="flex flex-col gap-4 p-5 transition-colors hover:bg-gray-50 dark:hover:bg-gray-950 md:flex-row md:items-center no-underline"
                                >
                                    <img class="h-12 w-12 rounded-2xl object-cover" :src="post.author_photo" :alt="post.author || post.title">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="truncate text-base font-extrabold text-gray-900 dark:text-white">{{ post.title }}</h3>
                                            <span
                                                v-for="tag in post.tags"
                                                :key="tag.id"
                                                class="rounded-full px-2 py-0.5 text-[11px] font-extrabold uppercase"
                                                :style="{ backgroundColor: `${tag.color || '#f5b800'}22`, color: tag.color || '#f5b800' }"
                                            >
                                                {{ tag.name }}
                                            </span>
                                        </div>
                                        <p class="mt-1 line-clamp-2 text-sm text-gray-500 dark:text-gray-400">{{ post.excerpt }}</p>
                                        <div class="mt-2 flex flex-wrap gap-3 text-xs font-bold text-gray-400">
                                            <span>{{ post.author || $t('dashboard.removed_author') }}</span>
                                            <span>{{ formatRelativeTime(post.created_at) }}</span>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2 text-center md:w-[220px]">
                                        <div class="rounded-xl bg-gray-50 dark:bg-gray-950 px-3 py-2">
                                            <div class="text-sm font-extrabold text-gray-900 dark:text-white">{{ post.likes_count }}</div>
                                            <div class="text-[10px] font-bold uppercase text-gray-400">{{ $t('dashboard.votes_short') }}</div>
                                        </div>
                                        <div class="rounded-xl bg-gray-50 dark:bg-gray-950 px-3 py-2">
                                            <div class="text-sm font-extrabold text-gray-900 dark:text-white">{{ post.comments_count }}</div>
                                            <div class="text-[10px] font-bold uppercase text-gray-400">{{ $t('dashboard.comments_short') }}</div>
                                        </div>
                                        <div class="rounded-xl bg-accent/15 px-3 py-2">
                                            <div class="text-sm font-extrabold text-accent">{{ post.hot_score }}</div>
                                            <div class="text-[10px] font-bold uppercase text-accent">{{ $t('dashboard.score') }}</div>
                                        </div>
                                    </div>
                                </a>

                                <div v-if="topPosts.length === 0" class="p-8 text-center text-sm text-gray-400">
                                    {{ $t('dashboard.no_posts') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <aside class="space-y-6">
                        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5">
                            <h2 class="text-base font-extrabold text-gray-900 dark:text-white">{{ $t('dashboard.top_tags_title') }}</h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $t('dashboard.top_tags_subtitle') }}</p>

                            <div class="mt-4 space-y-3">
                                <div v-for="tag in topTags" :key="tag.id">
                                    <div class="mb-1 flex items-center justify-between text-sm">
                                        <span class="font-bold text-gray-700 dark:text-gray-200">
                                            <i :class="tag.icon || 'fa-solid fa-tag'" class="mr-2" :style="{ color: tag.color || '#f5b800' }"></i>
                                            {{ tag.name }}
                                        </span>
                                        <span class="text-xs font-extrabold text-gray-400">{{ tag.posts_count }}</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                                        <div
                                            class="h-full rounded-full"
                                            :style="{ width: tagWidth(tag.posts_count), backgroundColor: tag.color || '#f5b800' }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 overflow-hidden">
                            <div class="p-5 border-b border-gray-200 dark:border-gray-700">
                                <h2 class="text-base font-extrabold text-gray-900 dark:text-white">{{ $t('dashboard.recent_activity_title') }}</h2>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $t('dashboard.recent_activity_subtitle') }}</p>
                            </div>

                            <div class="divide-y divide-gray-200 dark:divide-gray-800">
                                <a
                                    v-for="activity in recentActivity"
                                    :key="`${activity.type}-${activity.created_at}-${activity.description}`"
                                    :href="activity.href || '#'"
                                    class="flex gap-3 p-4 no-underline transition-colors hover:bg-gray-50 dark:hover:bg-gray-950"
                                >
                                    <div class="mt-0.5 h-9 w-9 shrink-0 rounded-xl bg-accent/15 text-accent flex items-center justify-center">
                                        <i :class="activity.icon" class="text-sm"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-sm font-extrabold text-gray-900 dark:text-white">{{ activityTitle(activity) }}</div>
                                        <div class="mt-0.5 truncate text-sm text-gray-500 dark:text-gray-400">{{ activity.description }}</div>
                                        <div class="mt-1 text-xs font-bold text-gray-400">
                                            {{ activity.user || $t('dashboard.system') }} · {{ formatRelativeTime(activity.human_time) }}
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </aside>
                </section>
            </main>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import moment from 'moment';
import 'moment/dist/locale/pt-br';
import Header from '@/Components/Header.vue';
import FloatingSidebarToggle from '@/Components/FloatingSidebarToggle.vue';

const props = defineProps({
    stats: { type: Object, required: true },
    growth: { type: Array, default: () => [] },
    topPosts: { type: Array, default: () => [] },
    topTags: { type: Array, default: () => [] },
    recentActivity: { type: Array, default: () => [] },
});

const { t, locale } = useI18n();
const isSidebarOpen = ref(true);

const navItems = computed(() => [
    { label: t('forum.dashboard'), href: '/dashboard', icon: 'fa-solid fa-table-cells-large', active: true },
    { label: t('forum.forum'), href: '/forum', icon: 'fa-solid fa-comments', active: false },
    { label: t('tags.manage_tags'), href: '/forum/tags', icon: 'fa-solid fa-tags', active: false },
    { label: t('dashboard.my_profile'), href: '/user/profile', icon: 'fa-solid fa-gear', active: false },
]);

const metrics = computed(() => [
    {
        label: t('dashboard.members'),
        value: props.stats.members,
        caption: t('dashboard.today_members_caption', { count: props.stats.today_members }),
        icon: 'fa-solid fa-users',
        iconBg: 'bg-sky-500/15',
        iconColor: 'text-sky-500',
    },
    {
        label: t('dashboard.discussions'),
        value: props.stats.posts,
        caption: t('dashboard.week_posts_caption', { count: props.stats.week_posts }),
        icon: 'fa-solid fa-message',
        iconBg: 'bg-accent/15',
        iconColor: 'text-accent',
    },
    {
        label: t('dashboard.comments'),
        value: props.stats.comments,
        caption: t('dashboard.week_comments_caption', { count: props.stats.week_comments }),
        icon: 'fa-solid fa-comment-dots',
        iconBg: 'bg-emerald-500/15',
        iconColor: 'text-emerald-500',
    },
    {
        label: t('dashboard.votes'),
        value: props.stats.votes,
        caption: t('dashboard.tags_caption', { count: props.stats.tags }),
        icon: 'fa-solid fa-arrow-up',
        iconBg: 'bg-violet-500/15',
        iconColor: 'text-violet-500',
    },
]);

const signals = computed(() => [
    { label: t('dashboard.today_posts'), value: props.stats.today_posts },
    { label: t('dashboard.today_members'), value: props.stats.today_members },
    { label: t('dashboard.tags'), value: props.stats.tags },
]);

const activityTitle = (activity) => {
    const titles = {
        post: t('dashboard.activity_new_post'),
        comment: t('dashboard.activity_recent_comment'),
        member: t('dashboard.activity_new_member'),
    };

    return titles[activity.type] || activity.title || t('dashboard.activity');
};

const maxGrowthValue = computed(() => {
    const values = props.growth.flatMap((day) => [day.posts, day.members, day.comments]);
    return Math.max(1, ...values);
});

const maxTagCount = computed(() => Math.max(1, ...props.topTags.map((tag) => tag.posts_count)));

const barHeight = (value) => `${Math.max(6, Math.round((value / maxGrowthValue.value) * 100))}%`;
const tagWidth = (value) => `${Math.max(8, Math.round((value / maxTagCount.value) * 100))}%`;
const formatRelativeTime = (date) => moment(date).locale(locale.value === 'pt-BR' ? 'pt-br' : 'en').fromNow();
</script>
