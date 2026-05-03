<template>
    <div ref="wrapperRef" class="rich-editor relative" :class="[`rich-editor-${mode}`, { 'rich-editor-error': hasError || isOverLimit }]">
        <div class="editor-shell">
            <div v-if="editor" class="editor-toolbar">
                <select
                    v-if="mode === 'post'"
                    class="toolbar-select"
                    :value="activeHeading"
                    @change="setHeading($event.target.value)"
                    title="Formato"
                >
                    <option value="paragraph">Normal</option>
                    <option value="1">H1</option>
                    <option value="2">H2</option>
                    <option value="3">H3</option>
                </select>

                <button type="button" class="toolbar-button" :class="{ active: editor.isActive('bold') }" title="Bold" @click="run('toggleBold')">
                    <i class="fa-solid fa-bold"></i>
                </button>
                <button type="button" class="toolbar-button" :class="{ active: editor.isActive('italic') }" title="Italic" @click="run('toggleItalic')">
                    <i class="fa-solid fa-italic"></i>
                </button>
                <button type="button" class="toolbar-button" :class="{ active: editor.isActive('underline') }" title="Underline" @click="run('toggleUnderline')">
                    <i class="fa-solid fa-underline"></i>
                </button>
                <button type="button" class="toolbar-button" :class="{ active: editor.isActive('strike') }" title="Strike" @click="run('toggleStrike')">
                    <i class="fa-solid fa-strikethrough"></i>
                </button>

                <span class="toolbar-separator"></span>

                <button type="button" class="toolbar-button" :class="{ active: editor.isActive('link') }" title="Link" @click="promptLink">
                    <i class="fa-solid fa-link"></i>
                </button>
                <button type="button" class="toolbar-button" title="Image" @click="imageHandler">
                    <i class="fa-regular fa-image"></i>
                </button>
                <button type="button" class="toolbar-button" :class="{ active: editor.isActive('bulletList') }" title="Bullet list" @click="run('toggleBulletList')">
                    <i class="fa-solid fa-list-ul"></i>
                </button>
                <button type="button" class="toolbar-button" :class="{ active: editor.isActive('orderedList') }" title="Ordered list" @click="run('toggleOrderedList')">
                    <i class="fa-solid fa-list-ol"></i>
                </button>
                <button type="button" class="toolbar-button" :class="{ active: editor.isActive('blockquote') }" title="Quote" @click="run('toggleBlockquote')">
                    <i class="fa-solid fa-quote-left"></i>
                </button>
                <button type="button" class="toolbar-button" :class="{ active: editor.isActive('code') }" title="Inline code" @click="run('toggleCode')">
                    <i class="fa-solid fa-code"></i>
                </button>
                <button type="button" class="toolbar-button" :class="{ active: editor.isActive('codeBlock') }" title="Code block" @click="run('toggleCodeBlock')">
                    <i class="fa-regular fa-file-code"></i>
                </button>

                <template v-if="mode === 'comment'">
                    <span class="toolbar-separator"></span>
                    <button type="button" class="toolbar-button text-button" title="@user" @click="insertMentionStarter">@</button>
                    <button type="button" class="toolbar-button text-button" title="/post" @click="openPostSearch">/post</button>
                </template>

                <button type="button" class="toolbar-button ml-auto" title="Clear formatting" @click="run('unsetAllMarks'); run('clearNodes')">
                    <i class="fa-solid fa-text-slash"></i>
                </button>
            </div>

            <EditorContent v-if="editor" :editor="editor" class="editor-content" />
        </div>

        <div
            v-if="showPasteMenu"
            class="absolute z-30 w-56 overflow-hidden rounded-2xl border border-gray-200 bg-white p-1 shadow-2xl dark:border-gray-700 dark:bg-gray-900"
            :style="{ left: pasteMenuPosition.left, top: pasteMenuPosition.top }"
        >
            <div class="px-3 py-2 text-[11px] font-black uppercase tracking-wide text-gray-400">{{ $t('post.paste_as') }}</div>
            <button
                type="button"
                class="paste-option"
                :title="$t('post.paste_as_url_tooltip')"
                :aria-label="$t('post.paste_as_url_tooltip')"
                @click="insertPendingUrl"
            >
                <i class="fa-solid fa-link"></i>
                {{ $t('post.paste_as_url') }}
            </button>
            <button
                type="button"
                class="paste-option"
                :title="$t('post.create_bookmark_tooltip')"
                :aria-label="$t('post.create_bookmark_tooltip')"
                @click="createBookmark"
            >
                <i class="fa-regular fa-bookmark"></i>
                {{ $t('post.create_bookmark') }}
            </button>
            <button
                v-if="pendingPostReference"
                type="button"
                class="paste-option"
                :title="$t('post.mention_post_tooltip')"
                :aria-label="$t('post.mention_post_tooltip')"
                @click="insertPendingPostReference"
            >
                <i class="fa-regular fa-message"></i>
                {{ $t('post.mention_post') }}
            </button>
        </div>

        <div
            v-if="showUserSearch"
            class="absolute left-3 right-3 top-14 z-40 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-900"
        >
            <div class="max-h-64 overflow-y-auto p-1">
                <button
                    v-for="user in userResults"
                    :key="user.id"
                    type="button"
                    class="w-full rounded-xl px-3 py-2.5 text-left hover:bg-accent/10"
                    @click="insertUserMention(user)"
                >
                    <div class="text-sm font-extrabold text-gray-900 dark:text-white">@{{ user.username }}</div>
                    <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ user.name || user.username }}</div>
                </button>
            </div>
        </div>

        <div
            v-if="showPostSearch"
            class="absolute left-3 right-3 top-14 z-40 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-900"
        >
            <div class="border-b border-gray-100 p-3 dark:border-gray-800">
                <div class="flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                    <i class="fa-solid fa-magnifying-glass text-xs text-gray-400"></i>
                    <input
                        ref="postSearchInput"
                        v-model="postSearchQuery"
                        type="text"
                        class="w-full border-0 bg-transparent p-0 text-sm text-gray-900 outline-none focus:ring-0 dark:text-white"
                        :placeholder="$t('post.search_posts')"
                        @keydown.esc.prevent="closePostSearch"
                    >
                </div>
            </div>
            <div class="max-h-72 overflow-y-auto p-1">
                <button
                    v-for="post in postResults"
                    :key="post.id"
                    type="button"
                    class="w-full rounded-xl px-3 py-2.5 text-left hover:bg-accent/10"
                    @click="insertPostReference(post)"
                >
                    <div class="text-sm font-extrabold text-gray-900 dark:text-white">{{ post.title }}</div>
                    <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ post.user_name }} · {{ post.excerpt }}</div>
                </button>
                <div v-if="postSearchQuery.length >= 2 && !postResults.length && !isSearchingPosts" class="px-3 py-5 text-center text-sm text-gray-400">
                    {{ $t('post.no_posts_found') }}
                </div>
                <div v-if="isSearchingPosts" class="px-3 py-5 text-center text-sm text-gray-400">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                </div>
            </div>
        </div>

        <div
            v-if="maxPlainTextLength"
            class="mt-1 text-xs"
            :class="isNearLimit || isOverLimit ? 'font-bold text-amber-600 dark:text-amber-400' : 'text-gray-400 dark:text-gray-500'"
        >
            {{ plainTextLength }} / {{ maxPlainTextLength }} {{ $t('comments.characters') }}
        </div>
    </div>
</template>

<script setup>
import { Editor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Image from '@tiptap/extension-image';
import Placeholder from '@tiptap/extension-placeholder';
import Underline from '@tiptap/extension-underline';
import TextAlign from '@tiptap/extension-text-align';
import CharacterCount from '@tiptap/extension-character-count';
import { Node, mergeAttributes } from '@tiptap/core';
import axios from 'axios';
import Swal from 'sweetalert2';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const URL_PATTERN = /(https?:\/\/[^\s<>"']+|\/posts\/\d+)/i;
const YOUTUBE_PATTERN = /https?:\/\/(?:www\.|m\.)?(?:youtube\.com\/watch\?[^ \n<>"']*v=|youtube\.com\/(?:shorts|embed)\/|youtu\.be\/)[a-zA-Z0-9_-]{6,20}[^\s<>"']*/i;

const PostReference = Node.create({
    name: 'postReference',
    group: 'block',
    atom: true,
    selectable: true,

    addAttributes() {
        return {
            href: { default: null },
            postId: { default: null },
            title: { default: '' },
            meta: { default: '' },
        };
    },

    parseHTML() {
        return [{
            tag: 'a.post-reference-card',
            getAttrs: element => ({
                href: element.getAttribute('href'),
                postId: element.getAttribute('data-post-id'),
                title: element.querySelector('.post-reference-title')?.textContent || element.textContent || '',
                meta: element.querySelector('.post-reference-meta')?.textContent || '',
            }),
        }];
    },

    renderHTML({ HTMLAttributes }) {
        const meta = HTMLAttributes.meta
            ? [['span', { class: 'post-reference-meta' }, HTMLAttributes.meta]]
            : [];

        return [
            'a',
            mergeAttributes({
                class: 'post-reference-card',
                href: HTMLAttributes.href,
                'data-post-id': HTMLAttributes.postId,
                target: '_blank',
                rel: 'noopener noreferrer',
            }),
            ['span', { class: 'post-reference-title' }, HTMLAttributes.title],
            ...meta,
        ];
    },
});

const LinkPreview = Node.create({
    name: 'linkPreview',
    group: 'block',
    atom: true,
    selectable: true,

    addAttributes() {
        return {
            href: { default: null },
            previewId: { default: null },
            url: { default: null },
            domain: { default: '' },
            title: { default: '' },
            description: { default: '' },
            imageUrl: { default: '' },
        };
    },

    parseHTML() {
        return [{
            tag: 'a.link-preview-card',
            getAttrs: element => ({
                href: element.getAttribute('href'),
                previewId: element.getAttribute('data-preview-id'),
                url: element.getAttribute('data-url'),
                domain: element.querySelector('.link-preview-domain')?.textContent || '',
                title: element.querySelector('.link-preview-title')?.textContent || element.textContent || '',
                description: element.querySelector('.link-preview-description')?.textContent || '',
                imageUrl: element.querySelector('.link-preview-image')?.getAttribute('src') || '',
            }),
        }];
    },

    renderHTML({ HTMLAttributes }) {
        const description = HTMLAttributes.description
            ? [['span', { class: 'link-preview-description' }, HTMLAttributes.description]]
            : [];
        const image = HTMLAttributes.imageUrl
            ? [['img', { class: 'link-preview-image', src: HTMLAttributes.imageUrl, alt: '' }]]
            : [];

        return [
            'a',
            mergeAttributes({
                class: 'link-preview-card',
                href: HTMLAttributes.href,
                'data-preview-id': HTMLAttributes.previewId,
                'data-url': HTMLAttributes.url || HTMLAttributes.href,
                target: '_blank',
                rel: 'noopener noreferrer nofollow ugc',
            }),
            ['span', {},
                ['span', { class: 'link-preview-domain' }, HTMLAttributes.domain],
                ['span', { class: 'link-preview-title' }, HTMLAttributes.title],
                ...description,
            ],
            ...image,
        ];
    },
});

const props = defineProps({
    content: {
        type: String,
        default: '',
    },
    contentType: {
        type: String,
        default: 'html',
    },
    hasError: {
        type: Boolean,
        default: false,
    },
    maxPlainTextLength: {
        type: Number,
        default: null,
    },
    mode: {
        type: String,
        default: 'post',
        validator: value => ['post', 'comment'].includes(value),
    },
    placeholder: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:content']);
const { t } = useI18n();

const editor = ref(null);
const wrapperRef = ref(null);
const postSearchInput = ref(null);
const htmlContent = ref(props.content || '');
const showPasteMenu = ref(false);
const pendingUrl = ref('');
const pendingRange = ref(null);
const pasteMenuPosition = ref({ left: '12px', top: '54px' });
const showPostSearch = ref(false);
const postSearchQuery = ref('');
const postResults = ref([]);
const isSearchingPosts = ref(false);
const postSearchTimer = ref(null);
const showUserSearch = ref(false);
const userSearchQuery = ref('');
const userResults = ref([]);
const userSearchTimer = ref(null);
const mentionRange = ref(null);
const processedPreviewUrls = ref(new Set());
const previewTimer = ref(null);

const plainTextLength = computed(() => editor.value?.storage.characterCount.characters() || getPlainText(htmlContent.value).length);
const isNearLimit = computed(() => props.maxPlainTextLength && plainTextLength.value >= Math.floor(props.maxPlainTextLength * 0.9));
const isOverLimit = computed(() => props.maxPlainTextLength && plainTextLength.value > props.maxPlainTextLength);
const pendingPostReference = computed(() => getInternalPostId(pendingUrl.value));
const activeHeading = computed(() => {
    if (!editor.value) return 'paragraph';
    for (const level of [1, 2, 3]) {
        if (editor.value.isActive('heading', { level })) return String(level);
    }
    return 'paragraph';
});

onMounted(() => {
    editor.value = new Editor({
        content: props.content || '',
        extensions: [
            StarterKit.configure({
                heading: { levels: [1, 2, 3] },
                link: false,
                underline: false,
            }),
            Underline,
            Link.configure({
                openOnClick: false,
                autolink: true,
                linkOnPaste: false,
                HTMLAttributes: {
                    rel: 'noopener noreferrer nofollow ugc',
                    target: '_blank',
                },
            }),
            Image.configure({
                inline: false,
                allowBase64: false,
                HTMLAttributes: {
                    class: 'editor-image',
                },
            }),
            TextAlign.configure({
                types: ['heading', 'paragraph'],
            }),
            Placeholder.configure({
                placeholder: props.placeholder || '',
            }),
            CharacterCount.configure({
                limit: props.maxPlainTextLength || null,
            }),
            PostReference,
            LinkPreview,
        ],
        editorProps: {
            attributes: {
                class: 'tiptap-prosemirror',
            },
            handlePaste(view, event) {
                return handlePaste(view, event);
            },
            handleKeyDown(view, event) {
                if (event.key === 'Escape') {
                    closeFloatingMenus();
                }
                return false;
            },
        },
        onUpdate({ editor: activeEditor, transaction }) {
            htmlContent.value = activeEditor.getHTML();
            emit('update:content', htmlContent.value);
            detectSlashAndMention();

            if (transaction.getMeta('paste')) {
                return;
            }

            clearTimeout(previewTimer.value);
            previewTimer.value = setTimeout(() => autoCreateYouTubeBookmark(), 350);
        },
        onSelectionUpdate() {
            detectSlashAndMention();
        },
    });
});

onBeforeUnmount(() => {
    clearTimeout(postSearchTimer.value);
    clearTimeout(userSearchTimer.value);
    clearTimeout(previewTimer.value);
    editor.value?.destroy();
});

watch(() => props.content, value => {
    if (!editor.value) return;
    if ((value || '') !== htmlContent.value) {
        editor.value.commands.setContent(value || '', false);
        htmlContent.value = value || '';
    }
});

watch(postSearchQuery, value => {
    clearTimeout(postSearchTimer.value);
    if (value.length < 2) {
        postResults.value = [];
        return;
    }
    postSearchTimer.value = setTimeout(() => searchPosts(value), 250);
});

watch(userSearchQuery, value => {
    clearTimeout(userSearchTimer.value);
    if (!value.length) {
        userResults.value = [];
        return;
    }
    userSearchTimer.value = setTimeout(() => searchUsers(value), 180);
});

const run = (command) => {
    editor.value?.chain().focus()[command]().run();
};

const setHeading = (value) => {
    if (!editor.value) return;
    if (value === 'paragraph') {
        editor.value.chain().focus().setParagraph().run();
        return;
    }
    editor.value.chain().focus().toggleHeading({ level: Number(value) }).run();
};

const promptLink = async () => {
    if (!editor.value) return;
    const current = editor.value.getAttributes('link').href || '';
    const result = await Swal.fire({
        title: t('post.paste_as_url'),
        input: 'url',
        inputValue: current,
        showCancelButton: true,
        confirmButtonText: 'OK',
        cancelButtonText: t('common.cancel'),
    });

    if (!result.isConfirmed) return;
    if (!result.value) {
        editor.value.chain().focus().unsetLink().run();
        return;
    }

    editor.value.chain().focus().extendMarkRange('link').setLink({ href: result.value }).run();
};

const handlePaste = (view, event) => {
    const text = event.clipboardData?.getData('text/plain')?.trim() || '';
    const url = text.match(URL_PATTERN)?.[0];
    if (!url) return false;

    event.preventDefault();
    event.stopPropagation();
    pendingUrl.value = url;
    pendingRange.value = {
        from: view.state.selection.from,
        to: view.state.selection.to,
    };

    const coords = view.coordsAtPos(view.state.selection.from);
    const bounds = wrapperRef.value?.getBoundingClientRect();
    pasteMenuPosition.value = {
        left: `${Math.max(12, Math.min((coords.left - (bounds?.left || 0)), 360))}px`,
        top: `${Math.max(54, coords.bottom - (bounds?.top || 0) + 8)}px`,
    };
    showPasteMenu.value = true;

    return true;
};

const insertPendingUrl = () => {
    const url = absoluteUrl(pendingUrl.value);
    processedPreviewUrls.value.add(url);
    insertLink(pendingUrl.value, url, pendingRange.value);
    closePasteMenu();
};

const createBookmark = async () => {
    const url = absoluteUrl(pendingUrl.value);
    await createBookmarkForUrl(url, pendingRange.value);
    closePasteMenu();
};

const createBookmarkForUrl = async (url, range = null) => {
    let data = null;

    try {
        const response = await axios.post('/links/preview', { url });
        data = response.data;
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: t('post.preview_failed'),
            text: error.response?.data?.message || t('forum.unexpected_error'),
        });
        return;
    }

    insertNode(linkPreviewNode(data), range);
    processedPreviewUrls.value.add(url);
};

const autoCreateYouTubeBookmark = async () => {
    if (!editor.value || showPasteMenu.value) return;
    const text = editor.value.getText();
    const match = text.match(YOUTUBE_PATTERN);
    if (!match) return;

    const rawUrl = match[0];
    const url = absoluteUrl(rawUrl);
    const currentHtml = editor.value.getHTML();
    if (processedPreviewUrls.value.has(url) || currentHtml.includes(`data-url="${escapeHtml(url)}"`)) return;

    const range = findTextRange(rawUrl);
    if (!range) return;
    await createBookmarkForUrl(url, range);
};

const insertPendingPostReference = async () => {
    const id = pendingPostReference.value;
    if (!id) return;
    try {
        const { data } = await axios.get(`/posts/search?query=${encodeURIComponent(id)}`);
        const post = data.find(item => Number(item.id) === Number(id)) || { id, title: `Post #${id}`, user_name: '', excerpt: '', url: `/posts/${id}` };
        insertPostReference(post, pendingRange.value);
    } finally {
        closePasteMenu();
    }
};

const closePasteMenu = () => {
    showPasteMenu.value = false;
    pendingUrl.value = '';
    pendingRange.value = null;
};

const closeFloatingMenus = () => {
    closePasteMenu();
    closePostSearch();
    showUserSearch.value = false;
};

const insertMentionStarter = () => {
    editor.value?.chain().focus().insertContent('@').run();
};

const detectSlashAndMention = () => {
    if (!editor.value || showPasteMenu.value) return;
    const { from } = editor.value.state.selection;
    const beforeCursor = editor.value.state.doc.textBetween(Math.max(0, from - 42), from, '\n', '\n');

    if (/(^|\s)\/post$/i.test(beforeCursor)) {
        openPostSearch();
        showUserSearch.value = false;
        return;
    }

    const mention = beforeCursor.match(/(^|\s)@([a-zA-Z0-9._]{1,30})$/);
    if (!mention) {
        showUserSearch.value = false;
        userResults.value = [];
        return;
    }

    mentionRange.value = {
        from: from - mention[2].length - 1,
        to: from,
    };
    userSearchQuery.value = mention[2];
    showUserSearch.value = true;
};

const searchUsers = async (query) => {
    try {
        const { data } = await axios.get(`/users/search?query=${encodeURIComponent(query)}`);
        userResults.value = data;
        showUserSearch.value = data.length > 0;
    } catch {
        userResults.value = [];
        showUserSearch.value = false;
    }
};

const insertUserMention = (user) => {
    if (!editor.value || !mentionRange.value) return;
    editor.value.chain().focus().insertContentAt(mentionRange.value, `@${user.username} `).run();
    showUserSearch.value = false;
    userResults.value = [];
    mentionRange.value = null;
};

const openPostSearch = () => {
    if (!editor.value) return;
    showPostSearch.value = true;
    postSearchQuery.value = '';
    postResults.value = [];
    nextTick(() => postSearchInput.value?.focus());
};

const closePostSearch = () => {
    showPostSearch.value = false;
    postSearchQuery.value = '';
    postResults.value = [];
};

const searchPosts = async (query) => {
    isSearchingPosts.value = true;
    try {
        const { data } = await axios.get(`/posts/search?query=${encodeURIComponent(query)}`);
        postResults.value = data;
    } catch {
        postResults.value = [];
    } finally {
        isSearchingPosts.value = false;
    }
};

const insertPostReference = (post, preferredRange = null) => {
    if (!editor.value) return;
    const commandRange = preferredRange || findCommandRange('/post');
    insertNode(postReferenceNode(post), commandRange);
    closePostSearch();
};

const insertLink = (text, url, range = null) => {
    if (!editor.value || !text) return;
    const safeRange = range || currentRange();
    const display = escapeHtml(String(text));
    const href = escapeHtml(url);
    editor.value
        .chain()
        .focus()
        .insertContentAt(safeRange, `<a href="${href}" target="_blank" rel="noopener noreferrer nofollow ugc">${display}</a>&nbsp;`)
        .run();
};

const insertHtml = (html, range = null) => {
    if (!editor.value) return;
    editor.value.chain().focus().insertContentAt(range || currentRange(), html).run();
};

const insertNode = (node, range = null) => {
    if (!editor.value) return;
    editor.value.chain().focus().insertContentAt(range || currentRange(), [node, { type: 'paragraph' }]).run();
};

const imageHandler = () => {
    const input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.accept = 'image/jpeg,image/png,image/jpg,image/gif';
    input.click();

    input.onchange = async () => {
        const file = input.files?.[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('image', file);

        try {
            const response = await axios.post('/posts/upload-image', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            editor.value?.chain().focus().setImage({ src: response.data.url }).run();
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: t('post.error_upload_image'),
                text: error.response?.data?.message || t('forum.unexpected_error'),
            });
        }
    };
};

const postReferenceNode = (post) => ({
    type: 'postReference',
    attrs: {
        href: `/posts/${post.id}`,
        postId: post.id,
        title: post.title || `Post #${post.id}`,
        meta: [post.user_name, post.excerpt].filter(Boolean).join(' · '),
    },
});

const linkPreviewNode = (preview) => ({
    type: 'linkPreview',
    attrs: {
        href: preview.url,
        previewId: preview.id,
        url: preview.url,
        domain: preview.domain || '',
        title: preview.title || preview.domain || preview.url,
        description: preview.description || '',
        imageUrl: preview.image_url || '',
    },
});

const findCommandRange = (command) => {
    if (!editor.value) return currentRange();
    const { from } = editor.value.state.selection;
    const beforeCursor = editor.value.state.doc.textBetween(Math.max(0, from - 20), from, '\n', '\n');
    const index = beforeCursor.toLowerCase().lastIndexOf(command.toLowerCase());
    if (index < 0) return currentRange();
    return {
        from: from - beforeCursor.length + index,
        to: from,
    };
};

const findTextRange = (needle) => {
    if (!editor.value || !needle) return null;
    let found = null;
    editor.value.state.doc.descendants((node, pos) => {
        if (found || !node.isText) return;
        const index = node.text.indexOf(needle);
        if (index >= 0) {
            found = {
                from: pos + index,
                to: pos + index + needle.length,
            };
        }
    });
    return found;
};

const currentRange = () => {
    const selection = editor.value?.state.selection;
    return {
        from: selection?.from || 0,
        to: selection?.to || selection?.from || 0,
    };
};

const getInternalPostId = (url) => {
    const match = String(url).match(/(?:^|\/)posts\/(\d+)/i);
    return match ? Number(match[1]) : null;
};

const absoluteUrl = (url) => {
    if (String(url).startsWith('/')) {
        return `${window.location.origin}${url}`;
    }
    return url;
};

const clearContent = () => {
    htmlContent.value = '';
    editor.value?.commands.clearContent(true);
};

const focusEditor = () => {
    editor.value?.chain().focus().run();
};

const getPlainText = (value = '') => String(value)
    .replace(/<[^>]*>/g, ' ')
    .replace(/&nbsp;/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();

const escapeHtml = (value = '') => String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

defineExpose({
    clearContent,
    focusEditor,
    getPlainText,
});
</script>

<style scoped>
.editor-shell {
    overflow: hidden;
    border: 1px solid #e5e7eb;
    border-radius: 0.875rem;
    background: #fff;
}

.editor-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.25rem;
    border-bottom: 1px solid #e5e7eb;
    background: rgba(249, 250, 251, 0.95);
    padding: 0.55rem;
}

.toolbar-button,
.toolbar-select {
    min-height: 2rem;
    border-radius: 0.6rem;
    border: 0;
    color: #4b5563;
    font-size: 0.88rem;
    font-weight: 800;
    transition: background-color 0.15s ease, color 0.15s ease;
}

.toolbar-button {
    display: inline-flex;
    min-width: 2rem;
    align-items: center;
    justify-content: center;
    padding: 0.45rem 0.55rem;
}

.toolbar-button:hover,
.toolbar-button.active {
    background: rgba(245, 184, 0, 0.16);
    color: #d4a000;
}

.text-button {
    padding-inline: 0.7rem;
}

.toolbar-select {
    background: transparent;
    padding: 0.35rem 0.5rem;
    outline: none;
}

.toolbar-separator {
    width: 1px;
    align-self: stretch;
    background: #e5e7eb;
    margin: 0 0.25rem;
}

.editor-content :deep(.tiptap-prosemirror) {
    min-height: 170px;
    max-height: 420px;
    overflow-y: auto;
    padding: 1rem;
    color: #111827;
    font-size: 0.95rem;
    line-height: 1.75;
    outline: none;
}

.rich-editor-comment .editor-content :deep(.tiptap-prosemirror) {
    min-height: 116px;
    max-height: 260px;
}

.editor-content :deep(.tiptap-prosemirror p.is-editor-empty:first-child::before) {
    content: attr(data-placeholder);
    float: left;
    height: 0;
    color: #9ca3af;
    pointer-events: none;
}

.editor-content :deep(.tiptap-prosemirror h1) {
    font-size: 1.65rem;
    font-weight: 900;
}

.editor-content :deep(.tiptap-prosemirror h2) {
    font-size: 1.35rem;
    font-weight: 900;
}

.editor-content :deep(.tiptap-prosemirror h3) {
    font-size: 1.15rem;
    font-weight: 900;
}

.editor-content :deep(.tiptap-prosemirror blockquote) {
    border-left: 3px solid #f5b800;
    margin: 0.75rem 0;
    padding-left: 1rem;
    color: #6b7280;
}

.editor-content :deep(.tiptap-prosemirror pre) {
    overflow-x: auto;
    border-radius: 0.85rem;
    background: #111827;
    color: #f9fafb;
    padding: 0.9rem;
}

.editor-content :deep(.tiptap-prosemirror code) {
    border-radius: 0.35rem;
    background: rgba(17, 24, 39, 0.08);
    padding: 0.1rem 0.35rem;
}

.editor-content :deep(.tiptap-prosemirror pre code) {
    background: transparent;
    padding: 0;
}

.editor-content :deep(.tiptap-prosemirror img) {
    max-width: 100%;
    border-radius: 0.9rem;
}

.editor-content :deep(.post-reference-card),
.editor-content :deep(.link-preview-card) {
    display: flex;
    gap: 0.9rem;
    margin: 0.75rem 0;
    padding: 0.9rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.95rem;
    background: #f9fafb;
    color: #111827;
    text-decoration: none;
}

.editor-content :deep(.post-reference-title),
.editor-content :deep(.link-preview-title) {
    display: block;
    font-weight: 900;
}

.editor-content :deep(.post-reference-meta),
.editor-content :deep(.link-preview-domain),
.editor-content :deep(.link-preview-description) {
    display: block;
    margin-top: 0.15rem;
    color: #6b7280;
    font-size: 0.82rem;
}

.editor-content :deep(.link-preview-image) {
    width: 96px;
    height: 72px;
    flex-shrink: 0;
    border-radius: 0.75rem;
    object-fit: cover;
}

.rich-editor-error .editor-shell {
    border-color: #f87171;
}

.dark .editor-shell {
    border-color: #374151;
    background: #111827;
}

.dark .editor-toolbar {
    border-color: #374151;
    background: rgba(31, 41, 55, 0.95);
}

.dark .toolbar-button,
.dark .toolbar-select {
    color: #d1d5db;
}

.dark .toolbar-separator {
    background: #374151;
}

.dark .editor-content :deep(.tiptap-prosemirror),
.dark .editor-content :deep(.tiptap-prosemirror p),
.dark .editor-content :deep(.tiptap-prosemirror h1),
.dark .editor-content :deep(.tiptap-prosemirror h2),
.dark .editor-content :deep(.tiptap-prosemirror h3),
.dark .editor-content :deep(.tiptap-prosemirror li),
.dark .editor-content :deep(.tiptap-prosemirror span) {
    color: #f9fafb;
}

.dark .editor-content :deep(.tiptap-prosemirror code) {
    background: rgba(255, 255, 255, 0.1);
}

.dark .editor-content :deep(.post-reference-card),
.dark .editor-content :deep(.link-preview-card) {
    border-color: #374151;
    background: #111827;
    color: #f9fafb;
}

.dark .editor-content :deep(.post-reference-meta),
.dark .editor-content :deep(.link-preview-domain),
.dark .editor-content :deep(.link-preview-description) {
    color: #9ca3af;
}

.paste-option {
    display: flex;
    width: 100%;
    align-items: center;
    gap: 0.6rem;
    border-radius: 0.75rem;
    padding: 0.65rem 0.75rem;
    text-align: left;
    font-size: 0.875rem;
    font-weight: 800;
    color: #4b5563;
}

.paste-option:hover {
    background: rgba(245, 184, 0, 0.14);
    color: #d4a000;
}

.dark .paste-option {
    color: #d1d5db;
}
</style>
