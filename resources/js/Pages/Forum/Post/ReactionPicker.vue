<template>
    <div ref="pickerRef" class="relative inline-flex">
        <button
            type="button"
            class="reaction-trigger inline-flex items-center gap-1.5 rounded-full px-1.5 py-1 transition"
            :class="activeReaction
                ? `${activeReaction.textClass} ${activeReaction.bgClass}`
                : 'text-gray-500 hover:bg-gray-100 hover:text-accent dark:text-gray-400 dark:hover:bg-gray-800'"
            :title="activeReaction ? activeReaction.activeLabel : $t('comments.choose_reaction')"
            :aria-label="activeReaction ? activeReaction.activeLabel : $t('comments.choose_reaction')"
            @click.stop="togglePicker"
        >
            <span
                class="flex h-5 w-5 items-center justify-center rounded-full transition-transform"
                :class="{ 'reaction-pop': justReacted }"
            >
                <i :class="activeReaction?.icon || 'fa-regular fa-heart'"></i>
            </span>
            <span>{{ count || 0 }}</span>
        </button>

        <Transition name="reaction-menu">
            <div
                v-if="isOpen"
                class="absolute bottom-full left-0 z-40 mb-2 flex items-center gap-1 rounded-full border border-gray-200 bg-white/95 p-1.5 shadow-2xl backdrop-blur dark:border-gray-700 dark:bg-gray-900/95"
            >
                <button
                    v-for="reaction in reactions"
                    :key="reaction.type"
                    type="button"
                    class="group relative flex h-10 w-10 items-center justify-center rounded-full text-lg transition hover:-translate-y-1 hover:scale-110"
                    :class="[
                        reaction.bgClass,
                        reaction.textClass,
                        reactionType === reaction.type ? 'ring-2 ring-offset-2 ring-accent dark:ring-offset-gray-900' : '',
                    ]"
                    :title="reaction.label"
                    :aria-label="reaction.label"
                    @click.stop="selectReaction(reaction.type)"
                >
                    <i :class="reaction.icon"></i>
                    <span class="pointer-events-none absolute -top-8 scale-90 rounded-full bg-gray-900 px-2 py-1 text-[10px] font-bold text-white opacity-0 transition group-hover:scale-100 group-hover:opacity-100 dark:bg-white dark:text-gray-900">
                        {{ reaction.label }}
                    </span>
                </button>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
    reactionType: {
        type: String,
        default: null,
    },
    count: {
        type: Number,
        default: 0,
    },
});

const emit = defineEmits(['select']);
const { t } = useI18n();
const isOpen = ref(false);
const justReacted = ref(false);
const pickerRef = ref(null);
let animationTimer = null;

const reactions = computed(() => [
    {
        type: 'liked',
        label: t('comments.reaction_liked'),
        activeLabel: t('comments.reaction_liked_active'),
        icon: 'fa-solid fa-thumbs-up',
        textClass: 'text-blue-500',
        bgClass: 'bg-blue-500/10',
    },
    {
        type: 'congrats',
        label: t('comments.reaction_congrats'),
        activeLabel: t('comments.reaction_congrats_active'),
        icon: 'fa-solid fa-hands-clapping',
        textClass: 'text-green-500',
        bgClass: 'bg-green-500/10',
    },
    {
        type: 'support',
        label: t('comments.reaction_support'),
        activeLabel: t('comments.reaction_support_active'),
        icon: 'fa-solid fa-hand-holding-heart',
        textClass: 'text-violet-500',
        bgClass: 'bg-violet-500/10',
    },
    {
        type: 'love',
        label: t('comments.reaction_love'),
        activeLabel: t('comments.reaction_love_active'),
        icon: 'fa-solid fa-heart',
        textClass: 'text-rose-500',
        bgClass: 'bg-rose-500/10',
    },
    {
        type: 'amazing',
        label: t('comments.reaction_amazing'),
        activeLabel: t('comments.reaction_amazing_active'),
        icon: 'fa-solid fa-lightbulb',
        textClass: 'text-amber-500',
        bgClass: 'bg-amber-500/10',
    },
    {
        type: 'funny',
        label: t('comments.reaction_funny'),
        activeLabel: t('comments.reaction_funny_active'),
        icon: 'fa-solid fa-face-laugh-beam',
        textClass: 'text-cyan-500',
        bgClass: 'bg-cyan-500/10',
    },
]);

const activeReaction = computed(() => reactions.value.find(reaction => reaction.type === props.reactionType));

const togglePicker = () => {
    isOpen.value = !isOpen.value;
};

const selectReaction = (reactionType) => {
    emit('select', reactionType);
    isOpen.value = false;
    justReacted.value = true;
    clearTimeout(animationTimer);
    animationTimer = setTimeout(() => {
        justReacted.value = false;
    }, 520);
};

const closeOnOutsideClick = (event) => {
    if (!pickerRef.value?.contains(event.target)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', closeOnOutsideClick);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', closeOnOutsideClick);
    clearTimeout(animationTimer);
});
</script>

<style scoped>
.reaction-menu-enter-active,
.reaction-menu-leave-active {
    transition: opacity 0.16s ease, transform 0.16s ease;
}

.reaction-menu-enter-from,
.reaction-menu-leave-to {
    opacity: 0;
    transform: translateY(0.35rem) scale(0.96);
}

.reaction-pop {
    animation: reaction-pop 0.52s cubic-bezier(0.2, 1.3, 0.3, 1);
}

@keyframes reaction-pop {
    0% {
        transform: scale(0.72) rotate(-8deg);
    }
    55% {
        transform: scale(1.28) rotate(7deg);
    }
    100% {
        transform: scale(1) rotate(0);
    }
}
</style>
