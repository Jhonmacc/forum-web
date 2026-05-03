<template>
    <div class="fixed inset-0 z-[100] bg-black/45 backdrop-blur-sm flex items-center justify-center">
        <div @click.stop class="bg-white dark:bg-gray-900 rounded-3xl w-[580px] max-w-[calc(100vw-40px)] max-h-[90vh] flex flex-col shadow-2xl">
            <div class="p-9 overflow-y-auto flex-1">
                <!-- Header -->
                <div class="flex justify-between items-center mb-7">
                    <h2 class="text-xl font-extrabold text-gray-900 dark:text-white">{{ $t('forum.new_discussion') }}</h2>
                    <button @click="$emit('close')"
                            class="w-[34px] h-[34px] rounded-full bg-gray-100 dark:bg-gray-800 border-none flex items-center justify-center text-lg text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                        ×
                    </button>
                </div>

                <!-- Title -->
                <label class="block text-xs font-bold tracking-wider text-gray-500 dark:text-gray-400 mb-2 uppercase">{{ $t('forum.title_label') }}</label>
                <input v-model="newPost.title"
                       :maxlength="limits.title"
                       :placeholder="$t('forum.title_placeholder')"
                       class="w-full py-3 px-4 rounded-xl border-[1.5px] border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-sm text-gray-900 dark:text-gray-100 outline-none focus:border-accent transition-colors placeholder-gray-400 dark:placeholder-gray-500"
                       :class="{ 'border-red-400': errors.title }" />
                <div class="mt-1 mb-4 flex items-center justify-between gap-3">
                    <p v-if="errors.title" class="text-red-500 text-sm">{{ errors.title }}</p>
                    <span class="ml-auto text-xs text-gray-400 dark:text-gray-500">{{ newPost.title.length }} / {{ limits.title }} {{ $t('comments.characters') }}</span>
                </div>

                <!-- Description -->
                <label class="block text-xs font-bold tracking-wider text-gray-500 dark:text-gray-400 mb-2 uppercase">{{ $t('forum.description_label') }}</label>
                <TextQuill ref="textQuill" v-model:content="newPost.description" mode="post" :max-plain-text-length="limits.descriptionText" class="mb-4" />
                <p v-if="errors.description" class="text-red-500 text-sm -mt-2 mb-4">{{ errors.description }}</p>

                <!-- Tags button -->
                <button @click="openTaggingModal"
                        class="px-5 py-2.5 rounded-xl bg-gray-500 dark:bg-gray-600 text-white text-sm font-semibold hover:bg-gray-600 dark:hover:bg-gray-500 transition-colors cursor-pointer mb-4">
                    {{ $t('forum.choose_tags') }}
                </button>

                <!-- Selected tags -->
                <div v-if="newPost.tags.length > 0" class="mb-2">
                    <p class="text-xs font-bold tracking-wider text-gray-500 dark:text-gray-400 mb-2 uppercase">{{ $t('forum.selected_tags') }}</p>
                    <div class="flex flex-wrap gap-2">
                        <span v-for="(tag, index) in newPost.tags" :key="index"
                              class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-white text-sm font-medium"
                              :style="{ backgroundColor: tag.color || '#ccc' }">
                            <i :class="tag.icon" class="text-xs"></i>
                            <span>{{ tag.name }}</span>
                        </span>
                    </div>
                </div>
                <p v-if="errors.tags" class="text-red-500 text-sm mt-1 mb-2">{{ errors.tags }}</p>
            </div>

            <!-- Actions -->
            <div class="px-9 pb-9 pt-6 flex gap-3 justify-end border-t border-gray-200 dark:border-gray-700">
                <button @click="$emit('close')"
                        class="px-6 py-[11px] rounded-xl border-[1.5px] border-gray-200 dark:border-gray-700 bg-transparent text-gray-500 dark:text-gray-400 text-sm font-semibold cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    {{ $t('common.cancel') }}
                </button>
                <button @click="submitPost"
                        :disabled="isCreateDisabled"
                        class="px-7 py-[11px] rounded-xl border-none bg-accent text-white text-sm font-bold cursor-pointer shadow-[0_4px_16px_rgba(245,184,0,0.35)] hover:bg-accent-dark transition-colors disabled:cursor-not-allowed disabled:opacity-60">
                    {{ $t('forum.create_post') }}
                </button>
            </div>

            <!-- Tagging Modal -->
            <tagging-modal v-if="showTaggingModal" @close="closeTaggingModal" @select-tags="setTags"
                :selected-tags="newPost.tags" />
        </div>
    </div>
</template>

<script>
import TaggingModal from './TaggingModal.vue';
import Swal from 'sweetalert2';
import TextQuill from '@/Components/TextQuill.vue';

const DRAFT_KEY = 'forum_new_post_draft';
const LIMITS = {
    title: 120,
    descriptionText: 10000,
    descriptionHtml: 20000,
};

export default {
    components: { TaggingModal, TextQuill },
    data() {
        return {
            newPost: {
                title: '',
                description: '',
                tags: [],
            },
            showTaggingModal: false,
            errors: {},
            limits: LIMITS,
        };
    },
    mounted() {
        this.loadDraft();
    },
    watch: {
        'newPost.title'() { this.saveDraft(); },
        'newPost.description'() { this.saveDraft(); },
        'newPost.tags': { deep: true, handler() { this.saveDraft(); } },
    },
    computed: {
        descriptionPlainText() {
            return this.getPlainText(this.newPost.description);
        },
        isCreateDisabled() {
            return this.newPost.title.length > this.limits.title
                || this.descriptionPlainText.length > this.limits.descriptionText
                || String(this.newPost.description).length > this.limits.descriptionHtml;
        },
    },
    methods: {
        saveDraft() {
            try {
                sessionStorage.setItem(DRAFT_KEY, JSON.stringify({
                    title: this.newPost.title,
                    description: this.newPost.description,
                    tags: this.newPost.tags,
                }));
            } catch {}
        },
        loadDraft() {
            try {
                const raw = sessionStorage.getItem(DRAFT_KEY);
                if (!raw) return;
                const draft = JSON.parse(raw);
                if (draft.title) this.newPost.title = draft.title;
                if (draft.description) this.newPost.description = draft.description;
                if (draft.tags?.length) this.newPost.tags = draft.tags;
            } catch {}
        },
        clearDraft() {
            sessionStorage.removeItem(DRAFT_KEY);
        },
        openTaggingModal() {
            this.showTaggingModal = true;
        },
        closeTaggingModal() {
            this.showTaggingModal = false;
        },
        setTags(tags) {
            this.newPost.tags = tags.map(tag => ({
                id: parseInt(tag.id, 10),
                name: tag.name || '',
                code: tag.code || null,
                color: tag.color || '#ccc',
                icon: tag.icon || 'fa-solid fa-tag',
                description: tag.description || '',
            }));
        },
        validateForm() {
            this.errors = {};
            if (!this.newPost.title.trim()) {
                this.errors.title = this.$t('forum.title_required');
            }
            if (this.newPost.title.length > this.limits.title) {
                this.errors.title = this.$t('forum.title_too_long', { max: this.limits.title });
            }
            const descriptionContent = this.getPlainText(this.newPost.description);
            if (!descriptionContent) {
                this.errors.description = this.$t('forum.description_required');
            }
            if (descriptionContent.length > this.limits.descriptionText || String(this.newPost.description).length > this.limits.descriptionHtml) {
                this.errors.description = this.$t('forum.description_too_long', { max: this.limits.descriptionText });
            }
            if (!this.newPost.tags.length) {
                this.errors.tags = this.$t('forum.tags_required');
            }
            return Object.keys(this.errors).length === 0;
        },
        submitPost() {
            if (!this.validateForm()) return;

            const postPayload = {
                title: this.newPost.title,
                description: this.newPost.description,
                tags: this.newPost.tags.map(tag => tag.id),
            };

            this.$inertia.post(route('posts.store'), postPayload, {
                onSuccess: () => {
                    Swal.fire({
                        icon: 'success',
                        title: this.$t('forum.post_created'),
                        showConfirmButton: false,
                        timer: 1500,
                    });
                    this.clearDraft();
                    this.clearForm();
                    this.$emit('close');
                    this.$emit('success');
                },
                onError: (errors) => {
                    Swal.fire({
                        icon: 'error',
                        title: this.$t('forum.post_create_error'),
                        text: Object.values(errors).flat().join(', ') || this.$t('forum.unexpected_error'),
                    });
                },
            });
        },
        clearForm() {
            this.newPost.title = '';
            this.newPost.description = '';
            this.$refs.textQuill.clearContent();
            this.newPost.tags = [];
        },
        getPlainText(value = '') {
            return String(value)
                .replace(/<[^>]*>/g, ' ')
                .replace(/&nbsp;/g, ' ')
                .replace(/\s+/g, ' ')
                .trim();
        },
    },
};
</script>
