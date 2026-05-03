<template>
    <div class="relative" ref="switcher">
        <button @click="open = !open"
                class="w-10 h-10 rounded-xl border-[1.5px] border-gray-200 dark:border-gray-600 bg-transparent flex items-center justify-center hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-sm font-bold text-gray-500 dark:text-gray-400">
            {{ currentFlag }}
        </button>
        <div v-if="open"
             class="absolute top-12 right-0 w-36 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-600 z-50 overflow-hidden">
            <button v-for="loc in locales" :key="loc.code"
                    @click="switchLocale(loc.code)"
                    class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center gap-2"
                    :class="{ 'bg-accent/10 font-bold': locale === loc.code }">
                <span>{{ loc.flag }}</span>
                <span>{{ loc.label }}</span>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useI18n } from 'vue-i18n';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const { locale } = useI18n();
const open = ref(false);
const switcher = ref(null);

const locales = [
    { code: 'pt-BR', label: 'Português', flag: '🇧🇷' },
    { code: 'en', label: 'English', flag: '🇺🇸' },
];

const currentFlag = computed(() => {
    const loc = locales.find(l => l.code === locale.value);
    return loc ? loc.flag : '🌐';
});

const switchLocale = async (code) => {
    locale.value = code;
    localStorage.setItem('locale', code);
    window.axios.defaults.headers.common['X-Locale'] = code;
    open.value = false;

    try {
        await axios.post('/locale', { locale: code });
    } catch {}

    router.reload();
};

const handleClickOutside = (event) => {
    if (switcher.value && !switcher.value.contains(event.target)) {
        open.value = false;
    }
};

onMounted(() => document.addEventListener('click', handleClickOutside));
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside));
</script>
