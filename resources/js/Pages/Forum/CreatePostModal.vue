<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center p-4">
        <div class="bg-white rounded shadow-lg w-full max-w-4xl text-black max-h-[90vh] flex flex-col modal-container">
            <div class="modal-content p-6 overflow-y-auto">
                <h2 class="text-xl text-gray-500 font-semibold mb-4">Nova Discussão</h2>
                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-gray-400">Título</label>
                    <input v-model="newPost.title" id="title"
                        class="mt-1 block w-full border-gray-300 rounded-3xl shadow-sm focus:border-yellow-500 focus:ring-yellow-500"
                        :class="{ 'border-red-500': errors.title }" required />
                    <p v-if="errors.title" class="text-red-500 text-sm mt-1">{{ errors.title }}</p>
                </div>
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-400">Descrição</label>
                    <TextQuill class="relative" ref="textQuill" v-model:content="newPost.description" />
                    <p v-if="errors.description" class="text-red-500 text-sm mt-1">{{ errors.description }}</p>
                </div>
                <div class="mb-4">
                    <button @click="openTaggingModal"
                        class="py-2 px-4 bg-gray-500 text-white rounded hover:bg-gray-600">
                        Escolha as Tags
                    </button>
                </div>

                <div v-if="newPost.tags.length > 0" class="mb-4">
                    <h1 class="font-medium text-gray-700">Tags Selecionadas:</h1>
                    <div class="flex flex-wrap gap-2 mt-2">
                        <span v-for="(tag, index) in newPost.tags" :key="index"
                            class="flex items-center space-x-2 px-3 py-1 rounded-full text-white font-medium"
                            :style="{ backgroundColor: tag.color || '#ccc' }">
                            <i :class="tag.icon" class="text-sm"></i>
                            <span>{{ tag.name }}</span>
                        </span>
                    </div>
                </div>

                <p v-if="errors.tags" class="text-red-500 text-sm mt-1">{{ errors.tags }}</p>
            </div>

            <div class="p-6 border-t border-gray-200 flex justify-end space-x-4">
                <button @click="$emit('close')" class="py-2 px-4 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                    Cancelar
                </button>
                <button @click="submitPost" class="py-2 px-4 bg-yellow-500 text-white rounded hover:bg-yellow-600">
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
import Swal from 'sweetalert2';
import axios from 'axios';
import TextQuill from '@/Components/TextQuill.vue';

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
        };
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
                icon: tag.icon || 'fa-solid fa-tag', // Inclui o ícone
                description: tag.description || '', // Inclui a descrição
            }));
        },
        validateForm() {
            this.errors = {};
            if (!this.newPost.title.trim()) {
                this.errors.title = 'Você precisa dar um título ao seu post.';
            }
            const descriptionContent = this.newPost.description.replace(/<[^>]+>/g, '').trim();
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
            this.$refs.textQuill.clearContent();
            this.newPost.tags = [];
        },
    },
};
</script>

<style scoped>
.modal-content {
    max-height: calc(90vh - 120px);
    /* Ajusta a altura considerando o espaço dos botões e padding */
}

/* Estilo para o contorno amarelo sombreado do modal */
.modal-container {
    border: 4px solid #f59e0b; /* Contorno amarelo */
    box-shadow: 0 0 15px rgba(245, 158, 11, 0.6); /* Sombra amarela suave */
}
</style>
