<template>
    <button
        v-if="collapsible && !isExpanded"
        type="button"
        @click="expandComposer"
        class="w-full rounded-full border border-gray-200 bg-white px-5 py-4 text-left text-sm font-medium text-gray-500 transition hover:border-accent/60 hover:text-gray-700 focus:border-accent focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:hover:text-gray-200"
    >
        {{ collapsedPlaceholder || placeholder }}
    </button>

    <div v-else class="relative rounded-2xl border border-gray-200 bg-gray-50 p-4 transition-all duration-300 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-start gap-3">
            <img
                v-if="user"
                :src="user.profile_photo_url"
                :alt="user.name"
                class="h-10 w-10 rounded-xl border border-gray-200 object-cover dark:border-gray-700"
            >
            <div class="min-w-0 flex-1">
                <TextQuill
                    ref="editorRef"
                    v-model:content="body"
                    mode="comment"
                    :placeholder="placeholder"
                    :max-plain-text-length="maxLength"
                    :has-error="isOverLimit"
                />

                <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div></div>
                    <div class="flex justify-end gap-2">
                        <button
                            v-if="showCancel"
                            type="button"
                            @click="resetAndCancel"
                            class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-bold text-gray-500 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            {{ $t('common.cancel') }}
                        </button>
                        <button
                            type="button"
                            @click="submit"
                            :disabled="processing || !hasMeaningfulContent || isOverLimit"
                            class="rounded-xl bg-accent px-4 py-2 text-sm font-bold text-white hover:bg-accent-dark disabled:opacity-60"
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
import TextQuill from '@/Components/TextQuill.vue';
import { computed, nextTick, ref, watch } from 'vue';

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
    collapsible: {
        type: Boolean,
        default: false,
    },
    collapsedPlaceholder: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue', 'submit', 'cancel']);

const body = ref(props.modelValue);
const editorRef = ref(null);
const isExpanded = ref(!props.collapsible || Boolean(props.modelValue));
const plainText = computed(() => getPlainText(body.value));
const isOverLimit = computed(() => plainText.value.length > props.maxLength);
const hasMeaningfulContent = computed(() => plainText.value.trim().length > 0 || /<(img|a)\b/i.test(body.value));

watch(() => props.modelValue, value => {
    if (value !== body.value) {
        body.value = value;
    }

    if (props.collapsible && !value && !props.processing) {
        isExpanded.value = false;
    }
});

watch(() => props.processing, value => {
    if (props.collapsible && !value && !body.value) {
        isExpanded.value = false;
    }
});

watch(body, value => {
    emit('update:modelValue', value);
});

const submit = () => {
    if (!hasMeaningfulContent.value || isOverLimit.value) return;
    emit('submit', {
        body: body.value,
        mentions: extractMentions(body.value),
    });
};

const resetAndCancel = () => {
    body.value = '';
    editorRef.value?.clearContent?.();
    if (props.collapsible) {
        isExpanded.value = false;
    }
    emit('cancel');
};

const expandComposer = async () => {
    isExpanded.value = true;
    await nextTick();
    editorRef.value?.focusEditor?.();
};

const extractMentions = (value) => {
    const plain = getPlainText(value);
    return [...plain.matchAll(/@([a-zA-Z0-9._]+)/g)].map(match => ({
        username: match[1],
        name: match[1],
    }));
};

const getPlainText = (value = '') => String(value)
    .replace(/<[^>]*>/g, ' ')
    .replace(/&nbsp;/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();
</script>
