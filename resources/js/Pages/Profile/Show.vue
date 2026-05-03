<template>
    <Head :title="$t('profile.settings_title')" />

    <div class="profile-settings relative flex h-screen overflow-hidden bg-gray-50 text-gray-900 dark:bg-gray-950 dark:text-gray-100">
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
                    class="w-full py-3 rounded-xl border-none bg-accent text-white font-bold text-sm flex items-center justify-center gap-2 shadow-[0_4px_20px_rgba(245,184,0,0.3)] hover:-translate-y-0.5 hover:shadow-[0_6px_24px_rgba(245,184,0,0.4)] transition-all no-underline"
                >
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    {{ $t('profile.back_to_forum') }}
                </a>
            </div>

            <nav class="flex-1 overflow-auto px-2.5">
                <p class="text-[10.5px] font-extrabold tracking-[0.1em] text-gray-400 dark:text-gray-500 px-3 py-2 uppercase">
                    {{ $t('forum.navigate') }}
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
                    {{ $t('profile.account') }}
                </p>
                <a
                    v-for="item in accountAnchors"
                    :key="item.href"
                    :href="item.href"
                    class="w-full flex items-center gap-2.5 px-3.5 py-2 rounded-[10px] text-[13.5px] mb-0.5 transition-all text-left no-underline text-gray-500 dark:text-gray-400 font-medium hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-accent"
                >
                    <i :class="item.icon" class="w-4 text-center"></i>
                    {{ item.label }}
                </a>
            </nav>

            <div class="px-4 py-3.5 border-t border-gray-200 dark:border-gray-700 flex items-center gap-2.5">
                <div class="w-[34px] h-[34px] rounded-full overflow-hidden shrink-0 ring-2 ring-accent/30">
                    <img class="w-full h-full object-cover" :src="user.profile_photo_url" :alt="user.name">
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-[13px] font-bold text-gray-900 dark:text-white truncate">{{ user.name }}</div>
                </div>
                <span class="text-accent">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </span>
            </div>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <Header />

            <main class="flex-1 overflow-auto">
                <section class="relative overflow-hidden border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
                    <div class="profile-sparkle profile-sparkle-one"></div>
                    <div class="profile-sparkle profile-sparkle-two"></div>
                    <div class="relative px-7 py-8">
                        <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
                            <div class="flex items-center gap-5">
                                <div class="relative">
                                    <div class="absolute inset-0 rounded-[28px] bg-accent/25 blur-xl animate-soft-pulse"></div>
                                    <img
                                        class="relative h-24 w-24 rounded-[28px] object-cover ring-4 ring-white dark:ring-gray-900 shadow-xl"
                                        :src="user.profile_photo_url"
                                        :alt="user.name"
                                    >
                                    <div class="absolute -right-1 -bottom-1 h-8 w-8 rounded-xl bg-accent text-white flex items-center justify-center shadow-lg animate-float">
                                        <i class="fa-solid fa-user-gear text-xs"></i>
                                    </div>
                                </div>

                                <div>
                                    <div class="inline-flex items-center gap-2 rounded-full bg-accent/15 px-3 py-1 text-xs font-bold text-accent mb-3">
                                        <i class="fa-solid fa-sparkles"></i>
                                        {{ $t('profile.profile_hub') }}
                                    </div>
                                    <h1 class="text-[28px] font-extrabold tracking-tight text-gray-900 dark:text-white">
                                        {{ user.name }}
                                    </h1>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 max-w-2xl">
                                        {{ $t('profile.settings_subtitle') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="px-7 py-7">
                    <div class="grid gap-6 2xl:grid-cols-[minmax(0,1fr)_320px]">
                        <div class="space-y-5">
                            <section
                                v-if="$page.props.jetstream.canUpdateProfileInformation"
                                id="profile-information"
                                class="profile-panel"
                            >
                                <UpdateProfileInformationForm :user="user" />
                            </section>

                            <section
                                v-if="$page.props.jetstream.canUpdatePassword"
                                id="profile-password"
                                class="profile-panel"
                            >
                                <UpdatePasswordForm />
                            </section>

                            <section
                                v-if="$page.props.jetstream.canManageTwoFactorAuthentication"
                                id="profile-security"
                                class="profile-panel"
                            >
                                <TwoFactorAuthenticationForm :requires-confirmation="confirmsTwoFactorAuthentication" />
                            </section>

                            <section id="profile-sessions" class="profile-panel">
                                <LogoutOtherBrowserSessionsForm :sessions="sessions" />
                            </section>

                            <section
                                v-if="$page.props.jetstream.hasAccountDeletionFeatures"
                                id="profile-danger"
                                class="profile-panel danger-panel"
                            >
                                <DeleteUserForm />
                            </section>
                        </div>

                        <aside class="space-y-4">
                            <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5 shadow-sm">
                                <div class="flex items-center gap-3">
                                    <div class="h-11 w-11 rounded-2xl bg-accent/15 text-accent flex items-center justify-center">
                                        <i class="fa-solid fa-shield-heart"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-sm font-extrabold text-gray-900 dark:text-white">{{ $t('profile.care_checklist') }}</h2>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $t('profile.care_checklist_subtitle') }}</p>
                                    </div>
                                </div>

                                <div class="mt-4 space-y-2">
                                    <div
                                        v-for="tip in securityTips"
                                        :key="tip.label"
                                        class="flex items-center gap-2 rounded-xl bg-gray-50 dark:bg-gray-950 px-3 py-2 text-sm text-gray-600 dark:text-gray-300"
                                    >
                                        <i :class="tip.done ? 'fa-solid fa-circle-check text-emerald-500' : 'fa-regular fa-circle text-gray-400'"></i>
                                        <span>{{ tip.label }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-accent/25 bg-accent/10 p-5 dark:bg-accent/15">
                                <div class="text-xs font-extrabold uppercase tracking-wider text-accent">{{ $t('profile.quick_tip') }}</div>
                                <p class="mt-2 text-sm leading-relaxed text-gray-700 dark:text-gray-200">
                                    {{ $t('profile.quick_tip_text') }}
                                </p>
                            </div>
                        </aside>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Header from '@/Components/Header.vue';
import FloatingSidebarToggle from '@/Components/FloatingSidebarToggle.vue';
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue';
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';

defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: { type: Array, default: () => [] },
});

const page = usePage();
const { t } = useI18n();
const isSidebarOpen = ref(true);
const user = computed(() => page.props.auth.user);
const twoFactorEnabled = computed(() => Boolean(user.value?.two_factor_enabled));

const navItems = computed(() => [
    ...(user.value?.is_admin ? [{ label: t('forum.dashboard'), href: '/dashboard', icon: 'fa-solid fa-table-cells-large', active: false }] : []),
    { label: t('forum.forum'), href: '/forum', icon: 'fa-solid fa-comments', active: false },
    ...(user.value?.is_admin ? [{ label: t('tags.manage_tags'), href: '/forum/tags', icon: 'fa-solid fa-tags', active: false }] : []),
    { label: t('forum.my_posts'), href: `/users/${user.value?.id}`, icon: 'fa-solid fa-user', active: false },
    { label: t('common.profile'), href: route('profile.show'), icon: 'fa-solid fa-gear', active: true },
]);

const accountAnchors = computed(() => [
    { label: t('profile.personal_data'), href: '#profile-information', icon: 'fa-solid fa-id-card' },
    { label: t('profile.password'), href: '#profile-password', icon: 'fa-solid fa-key' },
    { label: t('profile.security'), href: '#profile-security', icon: 'fa-solid fa-shield-halved' },
    { label: t('profile.sessions'), href: '#profile-sessions', icon: 'fa-solid fa-laptop' },
    { label: t('profile.danger_zone'), href: '#profile-danger', icon: 'fa-solid fa-triangle-exclamation' },
]);

const securityTips = computed(() => [
    { label: t('profile.email_registered'), done: Boolean(user.value?.email) },
    { label: t('profile.profile_photo_configured'), done: Boolean(user.value?.profile_photo_url) },
    { label: t('profile.two_factor_auth'), done: twoFactorEnabled.value },
]);
</script>

<style scoped>
.profile-panel {
    border: 1px solid rgb(229 231 235);
    border-radius: 18px;
    background: rgb(255 255 255);
    box-shadow: 0 14px 40px rgba(15, 23, 42, 0.06);
    overflow: hidden;
    transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease;
}

.dark .profile-panel {
    border-color: rgb(55 65 81);
    background: rgb(17 24 39);
    box-shadow: 0 14px 40px rgba(0, 0, 0, 0.24);
}

.profile-panel:hover {
    transform: translateY(-2px);
    border-color: rgba(245, 184, 0, 0.45);
    box-shadow: 0 18px 50px rgba(15, 23, 42, 0.1);
}

.danger-panel:hover {
    border-color: rgba(239, 68, 68, 0.45);
}

.profile-sparkle {
    position: absolute;
    width: 9px;
    height: 9px;
    border-radius: 2px;
    background: rgb(245 184 0);
    opacity: 0.55;
    rotate: 45deg;
    animation: float 4s ease-in-out infinite;
}

.profile-sparkle-one {
    top: 28px;
    right: 18%;
}

.profile-sparkle-two {
    bottom: 38px;
    right: 8%;
    animation-delay: 1.2s;
}

.animate-float {
    animation: float 3.4s ease-in-out infinite;
}

.animate-soft-pulse {
    animation: softPulse 3.6s ease-in-out infinite;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-6px);
    }
}

@keyframes softPulse {
    0%, 100% {
        opacity: 0.55;
        transform: scale(0.96);
    }
    50% {
        opacity: 0.85;
        transform: scale(1.04);
    }
}
</style>
