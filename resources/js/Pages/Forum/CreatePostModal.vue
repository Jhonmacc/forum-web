<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
        <div class="bg-white rounded shadow p-6 w-full max-w-4xl text-black">
            <h2 class="text-xl font-semibold mb-4">Criar Novo Post</h2>
            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700">Título</label>
                <input v-model="newPost.title" id="title"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :class="{ 'border-red-500': errors.title }" required />
                <p v-if="errors.title" class="text-red-500 text-sm mt-1">{{ errors.title }}</p>
            </div>
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700">Descrição</label>
                <div id="quill-editor" ref="quillEditor"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500"
                    style="max-height: 200px; overflow-y: auto;">
                </div>
                <p v-if="errors.description" class="text-red-500 text-sm mt-1">{{ errors.description }}</p>
            </div>
            <div class="mb-4">
                <button @click="openTaggingModal" class="py-2 px-4 bg-gray-500 text-white rounded hover:bg-gray-600">
                    Escolha as Tags
                </button>
            </div>

            <div v-if="newPost.tags.length > 0" class="mb-4">
                <h1 class="font-medium text-gray-700">Tags Selecionadas:</h1>
                <span class="list-disc list-inside">
                    <span v-for="(tag, index) in newPost.tags" :key="index"
                        class="flex items-center text-black space-x-2">
                        <span class="w-4 h-4 rounded-full" :style="{ backgroundColor: tag.color }"></span>
                        <span>{{ tag?.name }}</span>
                    </span>
                </span>
            </div>

            <p v-if="errors.tags" class="text-red-500 text-sm mt-1">{{ errors.tags }}</p>

            <div class="flex justify-end space-x-4">
                <button @click="$emit('close')" class="py-2 px-4 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                    Cancelar
                </button>
                <button @click="submitPost" class="py-2 px-4 bg-indigo-500 text-white rounded hover:bg-indigo-600">
                    Criar Post
                </button>
            </div>

            <tagging-modal v-if="showTaggingModal" @close="closeTaggingModal" @select-tags="setTags"
                :selected-tags="newPost.tags" />
        </div>
    </div>
</template>

<script>
import TaggingModal from './TaggingModal.vue';
import Swal from 'sweetalert2'; // Importando o SweetAlert2
import Quill from 'quill'; // Importar o Quill.js
import 'quill/dist/quill.snow.css'; // Estilo padrão do Quill.js
import axios from 'axios';

export default {
    components: { TaggingModal },
    data() {
        return {
            newPost: {
                title: '',
                description: '', // Conteúdo do editor
                tags: [],
            },
            quillEditor: null, // Referência ao editor Quill
            showTaggingModal: false,
            errors: {}, // Armazena mensagens de erro
        };
    },
    mounted() {
        // Inicializar o Quill.js
        const editorElement = this.$refs.quillEditor;
        if (editorElement) {
            this.quillEditor = new Quill(editorElement, {
                theme: 'snow', // Tema do Quill
                placeholder: 'Escreva a descrição do post aqui...',
                modules: {
                    toolbar: [
                        [{ header: [1, 2, false] }],
                        ['bold', 'italic', 'underline'],
                        ['link', 'image'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['clean'],
                    ],
                },
            });
        } else {
            console.error('Editor element not found');
        }
    },

    methods: {
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
            }));
        },
        validateForm() {
            this.errors = {};
            if (!this.newPost.title.trim()) {
                this.errors.title = 'Você precisa dar um título ao seu post.';
            }
            const descriptionContent = this.quillEditor.getText().trim();
            if (!descriptionContent) {
                this.errors.description = 'Você precisa dar uma descrição ao seu post.';
            }
            if (!this.newPost.tags.length) {
                this.errors.tags = 'Defina pelo menos uma tag ao seu post.';
            }
            return Object.keys(this.errors).length === 0;
        },
        async submitPost() {
            if (!this.validateForm()) {
                return;
            }

            // Obter o conteúdo HTML do editor Quill
            this.newPost.description = this.quillEditor.root.innerHTML;

            const postPayload = {
                title: this.newPost.title,
                description: this.newPost.description,
                tags: this.newPost.tags.map(tag => tag.id),
            };

            try {
                await this.$inertia.post(route('posts.store'), postPayload);

                Swal.fire({
                    icon: 'success',
                    title: 'Post criado com sucesso!',
                    showConfirmButton: false,
                    timer: 1500,
                });

                this.$emit('close');
                this.clearForm();
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro ao criar o post!',
                    text: error.response?.data.message || 'Ocorreu um erro inesperado.',
                });
            }
        },
        clearForm() {
            this.newPost.title = '';
            this.newPost.description = '';
            this.quillEditor.setContents([]); // Limpar o editor
            this.newPost.tags = [];
        },
    },
};
</script>
<style scoped>
#quill-editor {
    min-height: 150px;
    max-height: 400px;
    overflow-y: auto;
}
</style>
