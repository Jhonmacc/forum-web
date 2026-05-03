<template>
    <div class="tags-page relative flex h-screen overflow-hidden bg-gray-50 text-gray-900 dark:bg-gray-950 dark:text-gray-100">
        <FloatingSidebarToggle v-model:open="isSidebarOpen" />

        <aside
            class="shrink-0 overflow-hidden bg-white transition-all duration-300 ease-in-out dark:bg-gray-900"
            :class="isSidebarOpen ? 'w-[248px] border-r border-gray-200 dark:border-gray-700' : 'w-0 border-r-0 pointer-events-none'"
            :aria-hidden="!isSidebarOpen"
        >
            <div class="flex h-full w-[248px] flex-col overflow-hidden transition-opacity duration-200" :class="isSidebarOpen ? 'opacity-100' : 'opacity-0'">
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
                    @click="openCreateModal"
                    class="w-full py-3 rounded-xl border-none bg-accent text-white font-bold text-sm flex items-center justify-center gap-2 shadow-[0_4px_20px_rgba(245,184,0,0.3)] hover:-translate-y-0.5 hover:shadow-[0_6px_24px_rgba(245,184,0,0.4)] transition-all"
                >
                    <i class="fa-solid fa-plus text-xs"></i>
                    {{ $t('tags.new_tag') }}
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
                    {{ $t('tags.quick_filters') }}
                </p>
                <button
                    v-for="filter in quickFilters"
                    :key="filter.id"
                    @click="activeFilter = filter.id"
                    class="w-full flex items-center gap-2.5 px-3.5 py-2 rounded-[10px] border-none cursor-pointer text-[13.5px] mb-0.5 transition-all text-left"
                    :class="activeFilter === filter.id
                        ? 'bg-accent/15 text-accent font-bold'
                        : 'bg-transparent text-gray-500 dark:text-gray-400 font-medium hover:bg-gray-100 dark:hover:bg-gray-800'"
                >
                    <i :class="filter.icon" class="w-4 text-center"></i>
                    {{ filter.label }}
                    <span class="ml-auto text-[11px] font-bold px-1.5 py-0.5 rounded-full"
                          :class="activeFilter === filter.id ? 'bg-accent/25 text-accent' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400'">
                        {{ filter.count }}
                    </span>
                </button>
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

            <main class="flex-1 overflow-auto p-7">
                <section class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between mb-6">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-accent/15 px-3 py-1 text-xs font-bold text-accent mb-3">
                            <i class="fa-solid fa-tags"></i>
                            {{ $t('tags.taxonomy') }}
                        </div>
                        <h1 class="text-[26px] font-extrabold tracking-tight text-gray-900 dark:text-white">
                            {{ $t('tags.manage_tags') }}
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-2xl">
                            {{ $t('tags.subtitle') }}
                        </p>
                    </div>

                    <div class="flex gap-2">
                        <button
                            @click="goTo('/forum')"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border-[1.5px] border-gray-200 dark:border-gray-700 text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-900 transition-colors"
                        >
                            <i class="fa-solid fa-arrow-left text-xs"></i>
                            {{ $t('tags.back_to_forum') }}
                        </button>
                        <button
                            @click="openCreateModal"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-accent text-white text-sm font-bold shadow-[0_4px_16px_rgba(245,184,0,0.35)] hover:bg-accent-dark transition-colors"
                        >
                            <i class="fa-solid fa-plus text-xs"></i>
                            {{ $t('tags.create_tag') }}
                        </button>
                    </div>
                </section>

                <section class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl px-5 py-4">
                        <div class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ $t('tags.total_tags') }}</div>
                        <div class="mt-2 text-2xl font-extrabold text-gray-900 dark:text-white">{{ tags.length }}</div>
                    </div>
                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl px-5 py-4">
                        <div class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ $t('tags.used_tags') }}</div>
                        <div class="mt-2 text-2xl font-extrabold text-gray-900 dark:text-white">{{ usedTagsCount }}</div>
                    </div>
                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl px-5 py-4">
                        <div class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ $t('tags.unused_tags') }}</div>
                        <div class="mt-2 text-2xl font-extrabold text-gray-900 dark:text-white">{{ unusedTagsCount }}</div>
                    </div>
                </section>

                <section class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden">
                    <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                        <div class="relative flex-1 max-w-xl">
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input
                                v-model="search"
                                type="search"
                                :placeholder="$t('tags.search_placeholder')"
                                class="w-full rounded-xl border-[1.5px] border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 pl-10 pr-4 text-sm text-gray-900 dark:text-gray-100 outline-none focus:border-accent"
                            />
                        </div>

                        <div class="flex items-center gap-2">
                            <select
                                v-model="sortBy"
                                class="rounded-xl border-[1.5px] border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-3 text-sm font-semibold text-gray-600 dark:text-gray-300 focus:border-accent"
                            >
                                <option value="name">{{ $t('tags.sort_name') }}</option>
                                <option value="posts">{{ $t('tags.sort_usage') }}</option>
                                <option value="recent">{{ $t('tags.sort_recent') }}</option>
                            </select>
                            <button
                                @click="sortDirection = sortDirection === 'asc' ? 'desc' : 'asc'"
                                class="w-10 h-10 rounded-xl border-[1.5px] border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800"
                                :title="$t('tags.toggle_sort')"
                            >
                                <i :class="sortDirection === 'asc' ? 'fa-solid fa-arrow-up-short-wide' : 'fa-solid fa-arrow-down-wide-short'"></i>
                            </button>
                        </div>
                    </div>

                    <div v-if="filteredTags.length" class="divide-y divide-gray-100 dark:divide-gray-800">
                        <article
                            v-for="tag in filteredTags"
                            :key="tag.id"
                            class="px-5 py-4 flex flex-col gap-4 lg:flex-row lg:items-center hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                        >
                            <div class="flex items-center gap-4 flex-1 min-w-0">
                                <div
                                    class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 shadow-inner"
                                    :style="{ backgroundColor: withAlpha(tag.color || '#F5B800', 0.16), color: tag.color || '#F5B800' }"
                                >
                                    <i :class="tag.icon || 'fa-solid fa-tag'" class="text-lg"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h2 class="font-extrabold text-gray-900 dark:text-white truncate">{{ tag.name }}</h2>
                                        <span
                                            class="text-[11px] font-black tracking-wider uppercase px-2.5 py-0.5 rounded-full"
                                            :style="{ backgroundColor: withAlpha(tag.color || '#F5B800', 0.14), color: tag.color || '#F5B800' }"
                                        >
                                            {{ tag.code || codeFromName(tag.name) }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-1">
                                        {{ tag.description || $t('tags.no_description') }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-3 lg:w-[360px]">
                                <div>
                                    <div class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ $t('tags.usage') }}</div>
                                    <div class="text-sm font-extrabold text-gray-900 dark:text-white mt-1">
                                        {{ tag.posts_count || 0 }} {{ (tag.posts_count || 0) === 1 ? $t('tags.post') : $t('tags.posts') }}
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button
                                        @click="openEditModal(tag)"
                                        class="w-10 h-10 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-300 hover:text-accent hover:border-accent/40 hover:bg-accent/10 transition-colors"
                                        :title="$t('common.edit')"
                                    >
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button
                                        @click="confirmDelete(tag)"
                                        class="w-10 h-10 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-300 hover:text-red-500 hover:border-red-300 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors"
                                        :title="$t('common.delete')"
                                    >
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div v-else class="px-5 py-16 text-center">
                        <div class="w-14 h-14 mx-auto rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 mb-4">
                            <i class="fa-solid fa-tags text-xl"></i>
                        </div>
                        <div class="font-extrabold text-gray-900 dark:text-white">{{ $t('tags.empty_title') }}</div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $t('tags.empty_description') }}</p>
                    </div>
                </section>
            </main>
        </div>

        <Transition name="modal">
            <div v-if="showModal" class="fixed inset-0 z-[100] bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="w-full max-w-2xl bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-extrabold text-gray-900 dark:text-white">
                                {{ editingTag ? $t('tags.edit_tag') : $t('tags.create_tag') }}
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $t('tags.modal_subtitle') }}</p>
                        </div>
                        <button @click="closeModal" class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <form @submit.prevent="submitTag" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-[1fr_180px] gap-5">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold tracking-wider text-gray-500 dark:text-gray-400 uppercase mb-2">{{ $t('tags.name') }}</label>
                                    <input v-model="form.name" type="text" class="tag-input" :placeholder="$t('tags.name_placeholder')" autofocus>
                                    <p v-if="errors.name" class="tag-error">{{ errors.name }}</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold tracking-wider text-gray-500 dark:text-gray-400 uppercase mb-2">{{ $t('tags.code') }}</label>
                                    <input v-model="form.code" type="text" class="tag-input font-mono uppercase" :placeholder="codeFromName(form.name)">
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $t('tags.code_hint') }}</p>
                                    <p v-if="errors.code" class="tag-error">{{ errors.code }}</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold tracking-wider text-gray-500 dark:text-gray-400 uppercase mb-2">{{ $t('tags.icon') }}</label>
                                    <input v-model="form.icon" type="text" class="tag-input" placeholder="fa-solid fa-bug">
                                    <div class="flex flex-wrap gap-2 mt-2">
                                        <button
                                            v-for="icon in iconSuggestions"
                                            :key="icon"
                                            type="button"
                                            @click="form.icon = icon"
                                            class="w-9 h-9 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-300 hover:border-accent hover:text-accent"
                                            :class="{ 'border-accent text-accent bg-accent/10': form.icon === icon }"
                                        >
                                            <i :class="icon"></i>
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold tracking-wider text-gray-500 dark:text-gray-400 uppercase mb-2">{{ $t('tags.description') }}</label>
                                    <textarea v-model="form.description" rows="3" class="tag-input resize-none" :placeholder="$t('tags.description_placeholder')"></textarea>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold tracking-wider text-gray-500 dark:text-gray-400 uppercase mb-2">{{ $t('tags.color') }}</label>
                                    <input v-model="form.color" type="color" class="w-full h-11 rounded-xl border border-gray-200 bg-white p-1 dark:border-gray-700 dark:bg-gray-950">
                                    <div class="grid grid-cols-5 gap-2 mt-3">
                                        <button
                                            v-for="color in colorPresets"
                                            :key="color"
                                            type="button"
                                            @click="form.color = color"
                                            class="w-8 h-8 rounded-xl border-2"
                                            :class="form.color === color ? 'border-gray-900 dark:border-white' : 'border-transparent'"
                                            :style="{ backgroundColor: color }"
                                        ></button>
                                    </div>
                                    <p v-if="errors.color" class="tag-error">{{ errors.color }}</p>
                                </div>

                                <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                                    <div class="text-xs font-bold tracking-wider text-gray-400 uppercase mb-3">{{ $t('tags.preview') }}</div>
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-12 h-12 rounded-2xl flex items-center justify-center"
                                            :style="{ backgroundColor: withAlpha(form.color, 0.16), color: form.color }"
                                        >
                                            <i :class="form.icon || 'fa-solid fa-tag'" class="text-lg"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-extrabold text-gray-900 dark:text-white truncate">{{ form.name || $t('tags.new_tag') }}</div>
                                            <div class="text-[11px] font-black tracking-wider uppercase" :style="{ color: form.color }">
                                                {{ form.code || codeFromName(form.name) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 pt-5 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                            <button type="button" @click="closeModal" class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-sm font-bold text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                                {{ $t('common.cancel') }}
                            </button>
                            <button type="submit" :disabled="processing" class="px-5 py-2.5 rounded-xl bg-accent text-white text-sm font-bold hover:bg-accent-dark disabled:opacity-60">
                                <i v-if="processing" class="fa-solid fa-spinner fa-spin mr-2"></i>
                                {{ editingTag ? $t('tags.save_changes') : $t('tags.create_tag') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import Header from '@/Components/Header.vue';
import FloatingSidebarToggle from '@/Components/FloatingSidebarToggle.vue';
import { computed, reactive, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Swal from 'sweetalert2';

const props = defineProps({
    tags: {
        type: Array,
        default: () => [],
    },
});

const { t } = useI18n();
const page = usePage();

const tags = computed(() => props.tags);
const search = ref('');
const isSidebarOpen = ref(true);
const sortBy = ref('name');
const sortDirection = ref('asc');
const activeFilter = ref('all');
const showModal = ref(false);
const editingTag = ref(null);
const processing = ref(false);
const errors = reactive({});

const form = reactive({
    name: '',
    code: '',
    color: '#F5B800',
    icon: 'fa-solid fa-tag',
    description: '',
});

const colorPresets = ['#F5B800', '#2563EB', '#16A34A', '#7C3AED', '#DC2626', '#0891B2', '#EA580C', '#DB2777', '#4B5563', '#111827'];
const iconSuggestions = ['fa-solid fa-tag', 'fa-solid fa-bug', 'fa-solid fa-lightbulb', 'fa-solid fa-book-open', 'fa-solid fa-code', 'fa-solid fa-shield-halved', 'fa-solid fa-circle-question', 'fa-solid fa-rocket'];

const navItems = computed(() => [
    ...(page.props.auth.user?.is_admin ? [{ id: 'dashboard', label: t('forum.dashboard'), href: '/dashboard', icon: 'fa-solid fa-table-cells-large', active: false }] : []),
    { id: 'forum', label: t('forum.forum'), href: '/forum', icon: 'fa-solid fa-comments', active: false },
    ...(page.props.auth.user?.is_admin ? [{ id: 'tags', label: t('tags.manage_tags'), href: '/forum/tags', icon: 'fa-solid fa-tags', active: true }] : []),
    { id: 'meus', label: t('forum.my_posts'), href: `/users/${page.props.auth.user.id}`, icon: 'fa-solid fa-user', active: false },
]);

const usedTagsCount = computed(() => props.tags.filter(tag => (tag.posts_count || 0) > 0).length);
const unusedTagsCount = computed(() => props.tags.length - usedTagsCount.value);

const quickFilters = computed(() => [
    { id: 'all', label: t('tags.all_tags'), icon: 'fa-solid fa-layer-group', count: props.tags.length },
    { id: 'used', label: t('tags.used'), icon: 'fa-solid fa-link', count: usedTagsCount.value },
    { id: 'unused', label: t('tags.unused'), icon: 'fa-regular fa-circle', count: unusedTagsCount.value },
]);

const filteredTags = computed(() => {
    const term = search.value.trim().toLowerCase();

    return props.tags
        .filter((tag) => {
            if (activeFilter.value === 'used' && !(tag.posts_count > 0)) return false;
            if (activeFilter.value === 'unused' && (tag.posts_count > 0)) return false;
            if (!term) return true;

            return [tag.name, tag.code, tag.description, tag.icon]
                .filter(Boolean)
                .some(value => String(value).toLowerCase().includes(term));
        })
        .sort((a, b) => {
            const direction = sortDirection.value === 'asc' ? 1 : -1;

            if (sortBy.value === 'posts') {
                return ((a.posts_count || 0) - (b.posts_count || 0)) * direction;
            }

            if (sortBy.value === 'recent') {
                return ((a.id || 0) - (b.id || 0)) * direction;
            }

            return String(a.name || '').localeCompare(String(b.name || '')) * direction;
        });
});

const resetErrors = () => {
    Object.keys(errors).forEach(key => delete errors[key]);
};

const resetForm = () => {
    form.name = '';
    form.code = '';
    form.color = '#F5B800';
    form.icon = 'fa-solid fa-tag';
    form.description = '';
};

const openCreateModal = () => {
    editingTag.value = null;
    resetErrors();
    resetForm();
    showModal.value = true;
};

const openEditModal = (tag) => {
    editingTag.value = tag;
    resetErrors();
    form.name = tag.name || '';
    form.code = tag.code || codeFromName(tag.name);
    form.color = tag.color || '#F5B800';
    form.icon = tag.icon || 'fa-solid fa-tag';
    form.description = tag.description || '';
    showModal.value = true;
};

const closeModal = () => {
    if (processing.value) return;
    showModal.value = false;
};

const submitTag = () => {
    resetErrors();
    processing.value = true;

    const payload = {
        name: form.name,
        code: form.code || codeFromName(form.name),
        color: form.color,
        icon: form.icon,
        description: form.description,
    };

    const options = {
        preserveScroll: true,
        onSuccess: () => {
            showModal.value = false;
            resetForm();
        },
        onError: (serverErrors) => {
            Object.assign(errors, normalizeErrors(serverErrors));
        },
        onFinish: () => {
            processing.value = false;
        },
    };

    if (editingTag.value) {
        router.put(`/tags/${editingTag.value.id}`, payload, options);
        return;
    }

    router.post('/tags', payload, options);
};

const confirmDelete = async (tag) => {
    const result = await Swal.fire({
        title: t('tags.delete_title'),
        text: t('tags.confirm_delete_named', { name: tag.name }),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: t('common.yes_delete'),
        cancelButtonText: t('common.cancel'),
        reverseButtons: true,
        focusCancel: true,
        background: document.documentElement.classList.contains('dark') ? '#111827' : '#ffffff',
        color: document.documentElement.classList.contains('dark') ? '#f9fafb' : '#111827',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        customClass: {
            popup: 'tag-delete-alert',
            confirmButton: 'tag-delete-confirm',
            cancelButton: 'tag-delete-cancel',
        },
    });

    if (!result.isConfirmed) return;

    router.delete(`/tags/${tag.id}`, {
        preserveScroll: true,
        onBefore: () => {
            Swal.fire({
                title: t('common.loading'),
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                background: document.documentElement.classList.contains('dark') ? '#111827' : '#ffffff',
                color: document.documentElement.classList.contains('dark') ? '#f9fafb' : '#111827',
                didOpen: () => Swal.showLoading(),
            });
        },
        onSuccess: () => {
            Swal.fire({
                icon: 'success',
                title: t('tags.deleted_title'),
                text: t('tags.deleted_message'),
                timer: 1600,
                showConfirmButton: false,
                background: document.documentElement.classList.contains('dark') ? '#111827' : '#ffffff',
                color: document.documentElement.classList.contains('dark') ? '#f9fafb' : '#111827',
            });
        },
        onError: () => {
            Swal.fire({
                icon: 'error',
                title: t('common.error'),
                text: t('tags.delete_error'),
                background: document.documentElement.classList.contains('dark') ? '#111827' : '#ffffff',
                color: document.documentElement.classList.contains('dark') ? '#f9fafb' : '#111827',
            });
        },
    });
};

const goTo = (href) => {
    router.get(href);
};

const codeFromName = (name) => {
    if (!name) return 'NOVA_TAG';

    return String(name)
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-zA-Z0-9]+/g, '_')
        .replace(/^_+|_+$/g, '')
        .toUpperCase() || 'NOVA_TAG';
};

const withAlpha = (hex, alpha) => {
    const clean = String(hex || '#F5B800').replace('#', '');
    if (clean.length !== 6) return `rgba(245, 184, 0, ${alpha})`;

    const r = parseInt(clean.slice(0, 2), 16);
    const g = parseInt(clean.slice(2, 4), 16);
    const b = parseInt(clean.slice(4, 6), 16);

    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
};

const normalizeErrors = (serverErrors) => {
    return Object.fromEntries(
        Object.entries(serverErrors).map(([key, value]) => [
            key,
            Array.isArray(value) ? value[0] : value,
        ]),
    );
};
</script>

<style scoped>
.tags-page {
    color-scheme: light;
}

:global(.dark) .tags-page {
    color-scheme: dark;
}

.tag-input {
    width: 100%;
    border-radius: 0.75rem;
    border: 1.5px solid rgb(229 231 235);
    background: rgb(249 250 251);
    padding: 0.7rem 0.9rem;
    font-size: 0.875rem;
    color: rgb(17 24 39);
    outline: none;
    transition: border-color 0.2s ease, background-color 0.2s ease;
}

.tag-input::placeholder {
    color: rgb(156 163 175);
}

.tag-input:focus {
    border-color: #F5B800;
    background: white;
}

:global(.dark) .tag-input {
    border-color: rgb(55 65 81);
    background: rgb(31 41 55);
    color: rgb(243 244 246);
}

:global(.dark) .tag-input::placeholder {
    color: rgb(107 114 128);
}

:global(.dark) .tag-input:focus {
    background: rgb(17 24 39);
}

.tag-error {
    margin-top: 0.35rem;
    font-size: 0.8rem;
    color: rgb(239 68 68);
}

.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.18s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

:global(.tag-delete-alert) {
    border-radius: 1rem;
    border: 1px solid rgb(229 231 235);
}

:global(.dark .tag-delete-alert) {
    border-color: rgb(55 65 81);
}

:global(.tag-delete-confirm),
:global(.tag-delete-cancel) {
    border-radius: 0.75rem !important;
    padding: 0.7rem 1rem !important;
    font-weight: 800 !important;
}
</style>
