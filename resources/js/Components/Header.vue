<template>
    <header class="h-16 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 flex items-center px-7 gap-4 shrink-0">
        <!-- Search -->
        <div class="flex-1 relative">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 opacity-40" width="16" height="16" viewBox="0 0 16 16" fill="none">
                <circle cx="6.5" cy="6.5" r="4.5" stroke="currentColor" stroke-width="1.5"/>
                <path d="M10 10L14 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <input
                v-model="searchQuery"
                @input="handleSearch"
                :placeholder="$t('common.search_placeholder')"
                class="w-full max-w-[400px] py-2.5 pl-10 pr-4 rounded-xl border-[1.5px] border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-sm text-gray-900 dark:text-gray-100 outline-none focus:border-accent transition-colors placeholder-gray-400 dark:placeholder-gray-500"
            />
            <div v-if="isLoading" class="absolute right-3 top-1/2 -translate-y-1/2">
                <i class="fas fa-spinner fa-spin text-gray-400 text-sm"></i>
            </div>
            <div v-if="searchResults.length > 0 && searchQuery"
                 class="absolute top-full left-0 w-full max-w-[400px] bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-600 mt-1 max-h-60 overflow-y-auto z-50">
                <a v-for="(result, index) in searchResults" :key="index" :href="result.url"
                   class="block px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors first:rounded-t-2xl last:rounded-b-2xl">
                    {{ result.title }}
                </a>
            </div>
        </div>

        <!-- Language Switcher -->
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

        <!-- Notification bell -->
        <div class="relative" ref="notificationsAlert">
            <button @click.stop="toggleNotificationPopup"
                    class="w-10 h-10 rounded-xl border-[1.5px] border-gray-200 dark:border-gray-600 bg-transparent flex items-center justify-center relative hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" class="text-gray-500 dark:text-gray-400">
                    <path d="M9 2C6.79 2 5 3.79 5 6V10L3 12V13H15V12L13 10V6C13 3.79 11.21 2 9 2Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                    <path d="M7.5 14C7.5 14.83 8.17 15.5 9 15.5C9.83 15.5 10.5 14.83 10.5 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                <span v-if="unreadCount > 0" class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-accent border-2 border-white dark:border-gray-900"></span>
            </button>
            <div v-if="showPopup"
                 @click.stop
                 class="absolute top-12 right-0 w-[360px] max-w-[calc(100vw-32px)] bg-white dark:bg-gray-800 rounded-2xl shadow-xl border-[1.5px] border-gray-200 dark:border-gray-600 z-50 overflow-hidden">
                <div class="px-[18px] py-3.5 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <div>
                        <span class="font-bold text-[13px] text-gray-900 dark:text-gray-100">{{ $t('common.notifications') }}</span>
                        <span v-if="unreadCount > 0" class="ml-2 rounded-full bg-accent/15 px-2 py-0.5 text-[10px] font-black text-accent">
                            {{ unreadCount }}
                        </span>
                    </div>
                    <button
                        type="button"
                        class="h-8 w-8 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-green-500 dark:hover:bg-gray-700"
                        @click.stop="markAllAsRead"
                        :title="$t('common.mark_all_read')"
                    >
                        <i class="fa-solid fa-check text-xs"></i>
                    </button>
                </div>
                <div v-if="isLoadingNotifications" class="px-[18px] py-6 text-sm text-gray-400 text-center">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                </div>
                <div v-else-if="notifications.length === 0" class="px-[18px] py-6 text-sm text-gray-400 text-center">
                    {{ $t('common.no_notifications') }}
                </div>
                <div v-else class="max-h-[420px] overflow-y-auto">
                    <button
                        v-for="(notification, i) in notifications"
                        :key="notification.id"
                        type="button"
                        @click="openNotification(notification)"
                        class="w-full px-[18px] py-3.5 text-left flex gap-3 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/70"
                        :class="[
                            i < notifications.length - 1 ? 'border-b border-gray-100 dark:border-gray-700' : '',
                            !notification.read_at ? 'bg-accent/5' : 'bg-transparent'
                        ]"
                    >
                        <span
                            class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
                            :class="notificationTone(notification).bg"
                        >
                            <i :class="[notificationTone(notification).icon, notificationTone(notification).text, 'text-sm']"></i>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-[13px] leading-relaxed text-gray-700 dark:text-gray-200">
                                {{ notification.message }}
                            </span>
                            <span class="mt-1 block text-[11px] font-bold text-gray-400 dark:text-gray-500">
                                {{ formatNotificationDate(notification.created_at) }}
                            </span>
                        </span>
                        <span v-if="!notification.read_at" class="mt-1 h-2 w-2 shrink-0 rounded-full bg-accent"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- User -->
        <div class="flex items-center gap-2.5 pl-2 border-l border-gray-200 dark:border-gray-700 relative dropdown-container">
            <div class="w-9 h-9 rounded-full overflow-hidden cursor-pointer" @click="toggleDropdown">
                <img class="w-full h-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
            </div>
            <div class="cursor-pointer" @click="toggleDropdown">
                <div class="text-[13px] font-bold text-gray-900 dark:text-gray-100">{{ $page.props.auth.user.name }}</div>
            </div>
            <div v-show="isDropdownOpen"
                 class="absolute right-0 top-12 w-48 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-600 z-50 overflow-hidden">
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
    </header>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';

const page = usePage();

const emit = defineEmits(['search']);

const isDark = ref(false);

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

const logout = () => {
    router.post(route('logout'));
};

const notifications = ref([]);
const showPopup = ref(false);
const unreadCount = ref(0);
const isLoadingNotifications = ref(false);
const isDropdownOpen = ref(false);
const searchQuery = ref('');
const searchResults = ref([]);
const isLoading = ref(false);
const notificationsAlert = ref(null);

const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
};

const toggleNotificationPopup = () => {
    showPopup.value = !showPopup.value;
    if (showPopup.value) {
        loadNotifications();
    }
};

const markAllAsRead = async () => {
    try {
        const response = await axios.post('/notifications/mark-as-read');
        unreadCount.value = response.data.unread_count ?? 0;
        notifications.value = notifications.value.map(notification => ({
            ...notification,
            read_at: notification.read_at || new Date().toISOString(),
        }));
    } catch (error) {
        console.error('Error marking notifications as read:', error);
    }
};

const openNotification = async (notification) => {
    if (!notification.read_at) {
        notifications.value = notifications.value.map(item => item.id === notification.id
            ? { ...item, read_at: new Date().toISOString() }
            : item);
        unreadCount.value = Math.max(0, unreadCount.value - 1);

        try {
            const response = await axios.post(`/notifications/${notification.id}/read`);
            unreadCount.value = response.data.unread_count ?? unreadCount.value;
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    showPopup.value = false;

    if (notification.url) {
        router.get(notification.url);
    }
};

const loadNotifications = async () => {
    isLoadingNotifications.value = true;
    try {
        const response = await axios.get('/notifications');
        notifications.value = response.data.notifications || [];
        unreadCount.value = response.data.unread_count || 0;
    } catch (error) {
        console.error('Error loading notifications:', error);
        notifications.value = [];
        unreadCount.value = 0;
    } finally {
        isLoadingNotifications.value = false;
    }
};

const notificationTone = (notification) => {
    const kind = notification.kind || notification.type || '';

    if (String(kind).includes('mention')) {
        return { icon: 'fa-solid fa-at', bg: 'bg-blue-50 dark:bg-blue-950/40', text: 'text-blue-500' };
    }

    if (String(kind).includes('reply')) {
        return { icon: 'fa-solid fa-reply', bg: 'bg-emerald-50 dark:bg-emerald-950/40', text: 'text-emerald-500' };
    }

    if (String(kind).includes('comment')) {
        return { icon: 'fa-solid fa-comment', bg: 'bg-purple-50 dark:bg-purple-950/40', text: 'text-purple-500' };
    }

    return { icon: 'fa-solid fa-arrow-up', bg: 'bg-amber-50 dark:bg-amber-950/40', text: 'text-accent' };
};

const formatNotificationDate = (value) => {
    if (!value) return '';

    const date = new Date(value);
    const diffSeconds = Math.round((date.getTime() - Date.now()) / 1000);
    const ranges = [
        ['year', 31536000],
        ['month', 2592000],
        ['week', 604800],
        ['day', 86400],
        ['hour', 3600],
        ['minute', 60],
    ];

    const formatter = new Intl.RelativeTimeFormat(page.props.locale || 'pt-BR', { numeric: 'auto' });

    for (const [unit, seconds] of ranges) {
        const amount = Math.round(diffSeconds / seconds);
        if (Math.abs(amount) >= 1) {
            return formatter.format(amount, unit);
        }
    }

    return formatter.format(diffSeconds, 'second');
};

const handleSearch = async () => {
    emit('search', searchQuery.value);

    if (!searchQuery.value.trim()) {
        searchResults.value = [];
        return;
    }

    isLoading.value = true;
    try {
        const response = await axios.get(`/search-posts?query=${encodeURIComponent(searchQuery.value)}`);
        searchResults.value = response.data;
    } catch (error) {
        console.error('Error searching posts:', error);
        searchResults.value = [];
    } finally {
        isLoading.value = false;
    }
};

const handleClickOutside = (event) => {
    if (!event.target.closest('.dropdown-container')) {
        isDropdownOpen.value = false;
    }
    if (notificationsAlert.value && !notificationsAlert.value.contains(event.target)) {
        showPopup.value = false;
    }
};

onMounted(() => {
    initTheme();

    loadNotifications();

    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>
