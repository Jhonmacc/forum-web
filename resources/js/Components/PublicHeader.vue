<template>
    <header class="h-16 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 flex items-center px-7 gap-4 shrink-0">
        <!-- Logo -->
        <a href="/" class="flex items-center gap-2.5 shrink-0 no-underline">
            <div class="w-[34px] h-[34px] rounded-[10px] bg-accent flex items-center justify-center">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <path d="M2 4C2 2.9 2.9 2 4 2H14C15.1 2 16 2.9 16 4V11C16 12.1 15.1 13 14 13H10L6 16V13H4C2.9 13 2 12.1 2 11V4Z" fill="white"/>
                </svg>
            </div>
            <span class="font-extrabold text-base text-gray-900 dark:text-white tracking-tight">
                {{ $page.props.forumName || 'TechDevs' }}<span class="text-accent">.</span>
            </span>
        </a>

        <!-- Search -->
        <div class="flex-1 relative max-w-[400px] mx-auto">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 opacity-40" width="16" height="16" viewBox="0 0 16 16" fill="none">
                <circle cx="6.5" cy="6.5" r="4.5" stroke="currentColor" stroke-width="1.5"/>
                <path d="M10 10L14 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <input
                v-model="searchQuery"
                @input="handleSearch"
                :placeholder="$t('common.search_placeholder')"
                class="w-full py-2.5 pl-10 pr-4 rounded-xl border-[1.5px] border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-sm text-gray-900 dark:text-gray-100 outline-none focus:border-accent transition-colors placeholder-gray-400 dark:placeholder-gray-500"
            />
            <div v-if="searchResults.length > 0 && searchQuery"
                 class="absolute top-full left-0 w-full bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-600 mt-1 max-h-60 overflow-y-auto z-50">
                <a v-for="(result, index) in searchResults" :key="index" :href="result.url"
                   class="block px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors first:rounded-t-2xl last:rounded-b-2xl">
                    {{ result.title }}
                </a>
            </div>
        </div>

        <!-- Right side -->
        <div class="flex items-center gap-2.5 shrink-0">
            <!-- Language switcher -->
            <LanguageSwitcher />

            <!-- Theme toggle -->
            <button @click="toggleTheme"
                    class="w-10 h-10 rounded-xl border-[1.5px] border-gray-200 dark:border-gray-600 bg-transparent flex items-center justify-center hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-gray-500 dark:text-gray-400">
                <svg v-if="isDark" width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
                </svg>
                <svg v-else width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                </svg>
            </button>

            <!-- Auth buttons (guest) -->
            <template v-if="!isAuthenticated">
                <button @click="$emit('open-auth', 'login')"
                        class="px-5 py-2.5 rounded-xl border-[1.5px] border-gray-200 dark:border-gray-600 bg-transparent text-gray-700 dark:text-gray-200 text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors cursor-pointer">
                    {{ $t('auth.login') }}
                </button>
                <button @click="$emit('open-auth', 'register')"
                        class="px-5 py-2.5 rounded-xl border-none bg-accent text-white text-sm font-bold cursor-pointer shadow-[0_4px_16px_rgba(245,184,0,0.35)] hover:bg-accent-dark transition-colors">
                    {{ $t('auth.register') }}
                </button>
            </template>

            <!-- User menu (authenticated) -->
            <template v-else>
                <div class="flex items-center gap-2.5 pl-2 border-l border-gray-200 dark:border-gray-700 relative" ref="userMenu">
                    <div class="w-9 h-9 rounded-full overflow-hidden cursor-pointer" @click="showUserMenu = !showUserMenu">
                        <img class="w-full h-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                    </div>
                    <div class="cursor-pointer" @click="showUserMenu = !showUserMenu">
                        <div class="text-[13px] font-bold text-gray-900 dark:text-gray-100">{{ $page.props.auth.user.name }}</div>
                    </div>
                    <div v-show="showUserMenu"
                         class="absolute right-0 top-12 w-48 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-600 z-50 overflow-hidden">
                        <a href="/forum" class="block px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <i class="fas fa-comments mr-2 text-gray-400"></i> {{ $t('forum.forum') }}
                        </a>
                        <a :href="route('profile.show')" class="block px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <i class="fas fa-user mr-2 text-gray-400"></i> {{ $t('common.profile') }}
                        </a>
                        <form @submit.prevent="logout">
                            <button type="submit" class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <i class="fas fa-sign-out-alt mr-2 text-gray-400"></i> {{ $t('common.logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            </template>
        </div>
    </header>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import axios from 'axios';

const props = defineProps({
    isAuthenticated: { type: Boolean, default: false },
});

defineEmits(['open-auth', 'search']);

const isDark = ref(false);
const showUserMenu = ref(false);
const searchQuery = ref('');
const searchResults = ref([]);
const userMenu = ref(null);

const initTheme = () => {
    const saved = localStorage.getItem('theme');
    if (saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
        isDark.value = true;
    } else {
        document.documentElement.classList.remove('dark');
        isDark.value = false;
    }
};

const toggleTheme = () => {
    isDark.value = !isDark.value;
    if (isDark.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

const handleSearch = async () => {
    if (!searchQuery.value.trim()) {
        searchResults.value = [];
        return;
    }
    try {
        const response = await axios.get(`/search-posts?query=${encodeURIComponent(searchQuery.value)}`);
        searchResults.value = response.data;
    } catch {
        searchResults.value = [];
    }
};

const logout = () => {
    router.post(route('logout'));
};

const handleClickOutside = (event) => {
    if (userMenu.value && !userMenu.value.contains(event.target)) {
        showUserMenu.value = false;
    }
};

onMounted(() => {
    initTheme();
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>
