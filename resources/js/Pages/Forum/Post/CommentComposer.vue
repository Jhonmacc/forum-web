<template>
    <div class="relative rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
        <div class="flex items-start gap-3">
            <img v-if="user" :src="user.profile_photo_url" :alt="user.name" class="w-10 h-10 rounded-xl object-cover border border-gray-200 dark:border-gray-700">
            <div class="flex-1 min-w-0">
                <textarea
                    ref="textareaRef"
                    v-model="body"
                    @input="detectMention"
                    :maxlength="maxLength"
                    :placeholder="placeholder"
                    class="w-full min-h-[96px] resize-y rounded-xl border-[1.5px] border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3 text-sm text-gray-900 dark:text-gray-100 outline-none focus:border-accent placeholder-gray-400 dark:placeholder-gray-500"
                    :class="{ 'border-red-400 focus:border-red-400': isOverLimit }"
                ></textarea>

                <div v-if="showSuggestions" class="absolute left-16 right-4 top-[118px] z-20 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-xl overflow-hidden">
                    <button
                        v-for="suggestion in suggestions"
                        :key="suggestion.id"
                        type="button"
                        @click="addMention(suggestion)"
                        class="w-full px-4 py-2.5 text-left text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-accent/10 hover:text-accent"
                    >
                        @{{ suggestion.username }}
                    </button>
                </div>

                <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div
                        class="text-xs"
                        :class="isNearLimit || isOverLimit ? 'text-amber-600 dark:text-amber-400 font-bold' : 'text-gray-400 dark:text-gray-500'"
                    >
                        {{ body.length }} / {{ maxLength }} {{ $t('comments.characters') }}
                    </div>
                    <div class="flex justify-end gap-2">
                        <button
                            v-if="showCancel"
                            type="button"
                            @click="resetAndCancel"
                            class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-sm font-bold text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                        >
                            {{ $t('common.cancel') }}
                        </button>
                        <button
                            type="button"
                            @click="submit"
                            :disabled="processing || !body.trim() || isOverLimit"
                            class="px-4 py-2 rounded-xl bg-accent text-white text-sm font-bold hover:bg-accent-dark disabled:opacity-60"
                        >
                            <i v-if="processing" class="fa-solid fa-spinner fa-spin mr-2"></i>
                            {{ submitLabel }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import axios from 'axios';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    user: Object,
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: '',
    },
    submitLabel: {
        type: String,
        required: true,
    },
    processing: {
        type: Boolean,
        default: false,
    },
    showCancel: {
        type: Boolean,
        default: false,
    },
    maxLength: {
        type: Number,
        default: 2000,
    },
});

const emit = defineEmits(['update:modelValue', 'submit', 'cancel']);

const body = ref(props.modelValue);
const mentions = ref([]);
const suggestions = ref([]);
const showSuggestions = ref(false);
const textareaRef = ref(null);
const isAddingMention = ref(false);
const lastMentionQuery = ref('');
const isOverLimit = computed(() => body.value.length > props.maxLength);
const isNearLimit = computed(() => body.value.length >= Math.floor(props.maxLength * 0.9));

watch(() => props.modelValue, value => {
    body.value = value;
});

watch(body, value => {
    emit('update:modelValue', value);
    if (!value) {
        mentions.value = [];
        lastMentionQuery.value = '';
    }
});

const submit = () => {
    if (!body.value.trim() || isOverLimit.value) return;
    emit('submit', {
        body: body.value,
        mentions: mentions.value,
    });
};

const resetAndCancel = () => {
    body.value = '';
    mentions.value = [];
    showSuggestions.value = false;
    emit('cancel');
};

const detectMention = async (event) => {
    if (isAddingMention.value) return;

    const input = event.target.value;
    const cursorPosition = event.target.selectionStart;
    const mentionIndex = input.lastIndexOf('@', cursorPosition - 1);

    if (mentionIndex === -1) {
        showSuggestions.value = false;
        suggestions.value = [];
        return;
    }

    const afterAt = input.slice(mentionIndex + 1);
    const queryEnd = afterAt.indexOf(' ') !== -1 ? afterAt.indexOf(' ') : afterAt.length;
    const query = afterAt.slice(0, queryEnd);
    const isCompletedMention = query === lastMentionQuery.value && afterAt.includes(' ');
    const isActiveMention = cursorPosition > mentionIndex && (queryEnd === afterAt.length || cursorPosition <= mentionIndex + 1 + queryEnd);

    if (isCompletedMention || !isActiveMention || !query.length) {
        showSuggestions.value = false;
        suggestions.value = [];
        return;
    }

    try {
        const { data } = await axios.get(`/users/search?query=${encodeURIComponent(query)}`);
        suggestions.value = data;
        showSuggestions.value = data.length > 0;
    } catch (error) {
        showSuggestions.value = false;
        suggestions.value = [];
    }
};

const addMention = (user) => {
    const input = body.value;
    const cursorPosition = textareaRef.value?.selectionStart || input.length;
    const mentionIndex = input.lastIndexOf('@', cursorPosition - 1);
    const afterAt = input.slice(mentionIndex + 1);
    const queryEnd = afterAt.indexOf(' ') !== -1 ? afterAt.indexOf(' ') : afterAt.length;
    const queryLength = queryEnd + 1;

    isAddingMention.value = true;
    body.value = input.slice(0, mentionIndex) + `@${user.username} ` + input.slice(mentionIndex + queryLength);
    mentions.value.push({
        id: user.id,
        name: user.name || user.username,
        username: user.username,
    });
    showSuggestions.value = false;
    suggestions.value = [];
    lastMentionQuery.value = user.username;

    requestAnimationFrame(() => {
        isAddingMention.value = false;
        textareaRef.value?.focus();
    });
};
</script>
