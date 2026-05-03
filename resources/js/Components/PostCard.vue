<template>
    <div @click="$emit('click', post)"
         class="bg-white dark:bg-gray-900 rounded-2xl px-6 py-5 cursor-pointer flex gap-5 items-start border-[1.5px] border-transparent hover:border-accent/30 hover:shadow-lg dark:hover:shadow-accent/5 transition-all relative group">

        <!-- Pinned badge -->
        <div v-if="post.is_pinned" class="absolute top-3.5 right-[18px] text-[11px] font-bold text-accent bg-accent/10 px-2.5 py-0.5 rounded-full tracking-wider">
            {{ $t('forum.pinned') }}
        </div>

        <!-- Vote column -->
        <button
            @click.stop="handleVote"
            class="flex flex-col items-center gap-1 pt-0.5 min-w-[36px] rounded-xl px-2 py-1 transition-colors"
            :class="post.liked_by_current_user
                ? 'bg-accent/15 text-accent'
                : 'text-gray-400 dark:text-gray-500 group-hover:text-accent hover:bg-accent/10'"
        >
            <span class="text-lg leading-none">▲</span>
            <span class="text-[15px] font-bold text-gray-900 dark:text-white">{{ post.likes_count || 0 }}</span>
        </button>

        <!-- Avatar -->
        <div
            class="h-[34px] w-[34px] shrink-0 overflow-hidden rounded-full border border-gray-100 dark:border-gray-700"
            :class="hasProfilePhoto && !imageFailed ? 'bg-gray-100 dark:bg-gray-800' : 'flex items-center justify-center text-xs font-bold text-white'"
            :style="hasProfilePhoto && !imageFailed ? undefined : { background: avatarColor }"
        >
            <img
                v-if="hasProfilePhoto && !imageFailed"
                :src="profilePhotoUrl"
                :alt="post.user?.name || initials"
                class="h-full w-full object-cover"
                loading="lazy"
                @error="imageFailed = true"
            >
            <span v-else>{{ initials }}</span>
        </div>

        <!-- Content -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2.5 mb-[7px] flex-wrap">
                <span v-for="tag in post.tags" :key="tag.id"
                      class="text-[11px] font-bold tracking-wider px-2.5 py-0.5 rounded-full uppercase"
                      :style="{ background: getCatBg(tag.name), color: getCatText(tag.name) }">
                    {{ tag.name }}
                </span>
                <span class="text-xs text-gray-400 dark:text-gray-500">
                    {{ post.user?.name }} · {{ relativeTime }}
                </span>
            </div>
            <h3 class="text-[15.5px] font-bold text-gray-900 dark:text-white mb-1.5 leading-snug">
                {{ post.title?.length > 80 ? post.title.slice(0, 80) + '...' : post.title }}
            </h3>
            <p class="text-[13.5px] text-gray-500 dark:text-gray-400 leading-relaxed mb-3 line-clamp-2">
                {{ descriptionPreview }}
            </p>
            <div class="flex gap-[18px] items-center">
                <span class="text-[12.5px] text-gray-400 dark:text-gray-500 flex gap-1.5 items-center">
                    <span>💬</span>
                    <span class="font-semibold">{{ post.comments_count || 0 }}</span>
                    <span>{{ $t('forum.replies') }}</span>
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import moment from 'moment';
import 'moment/dist/locale/pt-br';

const { locale } = useI18n();

const props = defineProps({
    post: { type: Object, required: true },
    isAuthenticated: { type: Boolean, default: true },
});

const emit = defineEmits(['vote', 'request-auth', 'click']);
const imageFailed = ref(false);

const CAT_COLORS = {
    'Suporte':  { bg: '#e0eafc', text: '#2d5aa0' },
    'Ideias':   { bg: '#dcf5e0', text: '#1d7a3a' },
    'Artigo':   { bg: '#ece0fc', text: '#5a2d9c' },
    'Artigos':  { bg: '#ece0fc', text: '#5a2d9c' },
    'Bug':      { bg: '#fce0df', text: '#a03030' },
    'Bugs':     { bg: '#fce0df', text: '#a03030' },
};

const AVATAR_HUES = ['#4a90d9', '#3dab5e', '#d4a028', '#8b5ec8', '#c84d7a', '#d05a3a'];

const getCatBg = (name) => CAT_COLORS[name]?.bg || '#e8e7e3';
const getCatText = (name) => CAT_COLORS[name]?.text || '#6b6b80';

const avatarColor = computed(() => AVATAR_HUES[(props.post.id || 0) % AVATAR_HUES.length]);
const hasProfilePhoto = computed(() => Boolean(props.post.user?.profile_photo_path && props.post.user?.profile_photo_url));
const profilePhotoUrl = computed(() => props.post.user?.profile_photo_url || '');

const initials = computed(() => {
    const name = props.post.user?.name || props.post.user?.username;
    if (!name) return '?';
    const parts = name.trim().split(/\s+/);
    return parts.length >= 2
        ? (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
        : name.slice(0, 2).toUpperCase();
});

watch(() => profilePhotoUrl.value, () => {
    imageFailed.value = false;
});

const relativeTime = computed(() => {
    return moment(props.post.created_at).locale(locale.value === 'pt-BR' ? 'pt-br' : 'en').fromNow();
});

const descriptionPreview = computed(() => {
    const plainText = (props.post.description || '').replace(/<[^>]+>/g, '');
    return plainText.length > 150 ? plainText.slice(0, 150) + '...' : plainText;
});

const handleVote = () => {
    if (props.isAuthenticated) {
        emit('vote', props.post);
    } else {
        emit('request-auth');
    }
};
</script>
