<script setup>
import Header from '@/Components/Header.vue';
</script>

<template>
    <div class="bg-white min-h-screen">
        <!-- Componente do Cabecalho e Menu Header -->
        <Header />

        <!-- Tags do Post -->
        <div class="w-full bg-lime-500 p-4 text-gray-700">
            <div class="flex items-center justify-center mb-4">
                <span v-for="(tag, index) in post?.tags" :key="index" :style="{ backgroundColor: tag.color }"
                    class="items-center text-white px-2 py-1 rounded-md text-sm mr-2"
                    style="text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6);">
                    <i :class="tag.icon" class="text-sm text-white"></i> {{ tag.name }}
                </span>
            </div>

            <!-- Título do Post -->
            <div class="flex items-center justify-center">
                <div class="flex items-center space-x-6">
                    <h2 class="text-2xl font-bold text-white mb-4 text-center">{{ post?.title }}</h2>
                </div>
            </div>
        </div>
        <!-- Elementos de Autor e Data (Alinhados à Esquerda) -->
        <div
            class="max-w-6xl mx-auto flex space-x-2 hover:-translate-y-1 transition-transform transform translate-y-1 translate-x-20">
            <img
                class="w-16 h-16 cursor-pointer rounded-full object-cover
                hover:underline
                hover:-translate-y-1 transition-transform transform translate-y-4 -translate-x-2"
                :src="$page.props.post.user.profile_photo_url" :alt="$page.props.post.user.name">
            <div class="flex items-center space-x-2">
                <button
                    class="hover:underline hover:-translate-y-1 transition-transform transform">{{
                        post?.user?.name }}
                </button>
                <button
                    class="text-blue-600 text-sm cursor-pointer hover:underline hover:-translate-y-1 transition-transform transform"
                    v-tooltip.focus.top="formatDate(post?.created_at)">
                    {{ formatRelativeTime(post?.created_at) }}
                </button>
            </div>
        </div>
        <div class="p-6 py-0 bg-white shadow-md rounded-lg max-w-4xl mx-auto">
            <!-- Descrição do Post -->
            <div class="text-gray-700">
                <article v-html="post?.description" class="prose prose-sm max-w-full text-gray-700 mx-auto"></article>
            </div>

        <div class="flex justify-end mt-8 mb-4 p-4 relative dropdown-container">
    <!-- Botão principal com os três pontos -->
                <button @click="toggleDropdown" class="p-2 bg-gray-200 text-gray-700 rounded-full hover:bg-gray-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 3a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 7a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 7a1.5 1.5 0 110-3 1.5 1.5 0 010 3z" />
                </svg>
                </button>

        <!-- Dropdown menu -->
        <div v-if="showDropdown"
            class="absolute top-full mt-2 w-32 bg-white border border-gray-300 rounded-lg shadow-lg z-10">
        <button @click="editPost" class="flex items-center w-full px-4 py-2 text-gray-700 hover:bg-gray-100">
            <i class="fa-regular fa-pen-to-square mr-1"></i>
            Editar
        </button>
        <button @click="deletePost" class="flex items-center w-full px-4 py-2 text-gray-700 hover:bg-gray-100">
            <i class="fa-regular fa-trash-can mr-1"></i>
            Delete
        </button>
        </div>
    </div>

        </div>
        <div class="p-6 py-5 mt-10 bg-white shadow-md rounded-lg max-w-4xl mx-auto">
            <div class="card-columns">
                <h1>{{ post?.title }}</h1>
            </div>
        </div>
        <!-- Modal de Edição -->
        <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
            <div class="bg-white rounded shadow p-6 w-full max-w-4xl text-black">
                <h2 class="text-xl font-semibold mb-4">Editar Post</h2>

                <!-- Título do Post -->
                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-gray-700">Título</label>
                    <input v-model="postToEdit.title" id="title"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :class="{ 'border-red-500': errors.title }" required />
                    <p v-if="errors.title" class="text-red-500 text-sm mt-1">{{ errors.title }}</p>
                </div>

                <!-- Descrição do Post com Quill Editor -->
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700">Descrição</label>
                    <div id="quill-editor" ref="quillEditor"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500"
                        style="max-height: 200px; overflow-y: auto;">
                    </div>
                    <p v-if="errors.description" class="text-red-500 text-sm mt-1">{{ errors.description }}</p>
                </div>

                <div class="mb-4">
                    <button @click="openTaggingModal"
                        class="py-2 px-4 bg-gray-500 text-white rounded hover:bg-gray-600">
                        Escolha as Tags
                    </button>
                </div>

                <!-- Exibição das Tags Selecionadas -->
                <div v-if="postToEdit.tags.length > 0" class="mb-4">
                    <h3 class="font-medium text-gray-700">Tags Selecionadas:</h3>
                    <div class="container w-full h-1/24 grid grid-cols-4">
                        <span v-for="(tag, index) in postToEdit.tags" :key="index"
                            class="flex items-center space-x-2 w-1/2">
                            <span class="w-4 h-4 rounded-full" :style="{ backgroundColor: tag.color }"></span>
                            <span>{{ tag?.name }}</span>
                        </span>
                    </div>
                </div>

                <p v-if="errors.tags" class="text-red-500 text-sm mt-1">{{ errors.tags }}</p>

                <!-- Botões de Ação -->
                <div class="flex justify-end space-x-4">
                    <button @click="closeEditModal"
                        class="py-2 px-4 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                        Cancelar
                    </button>
                    <button @click="updatePost" class="py-2 px-4 bg-indigo-500 text-white rounded hover:bg-indigo-600">
                        Atualizar Post
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal de Seleção de Tags -->
        <tagging-modal v-if="showTaggingModal" @close="closeTaggingModal" @select-tags="setTags"
            :selected-tags="postToEdit.tags" />

    </div>
</template>
<script>
import moment from 'moment';
import Quill from 'quill';
import 'quill/dist/quill.snow.css';
import Tooltip from 'primevue/tooltip';
import TaggingModal from './TaggingModal.vue';
import Swal from 'sweetalert2';
import axios from 'axios';
import 'moment/dist/locale/pt-br';

export default {
    components: { TaggingModal, Tooltip },
    props: {
        posts: Object, // Recebe posts do Laravel/Inertia
    },
    data() {
        return {
            post: null,
            postToEdit: { title: '', description: '', tags: [] },
            quillEditor: null,
            showEditModal: false,
            showTaggingModal: false,
            errors: {},
            formattedDate: '',
            showTooltip: false,
            showDropdown: false, // Controla a exibição do dropdown
        };
    },
    methods: {
        async fetchPost() {
            try {
                const response = await axios.get(`/posts/${window.location.pathname.split('/')[2]}`);
                this.post = response.data;
                this.formattedDate = moment(this.post.created_at).format('DD/MM/YYYY');
            } catch (error) {
                console.error('Erro ao carregar o post:', error);
                this.post = null;
                Swal.fire({
                    icon: 'error',
                    title: 'Erro ao carregar o post!',
                    text: error.response?.data.message || 'Ocorreu um erro inesperado.',
                });
            }
        },

        formatDate(date) {
            return moment(date).locale('pt-BR').format('dddd, D MMMM, YYYY HH:mm:ss A');
        },

        formatRelativeTime(date) {
            return moment(date).fromNow();
        },

        // Função para abrir o modal de edição
        editPost() {
            // Atualiza os dados de postToEdit com as informações mais recentes
            this.postToEdit = { ...this.post };

            // Abre o modal de edição
            this.showEditModal = true;
            this.showDropdown = false; // Fecha o dropdown ao abrir o modal
        },

        // Função para alternar a exibição do dropdown
        toggleDropdown() {
            this.showDropdown = !this.showDropdown;
        },
        // Função para fechar o dropdown
        closeDropdown(event) {
            if (!event.target.closest('.dropdown-container')) {
                this.showDropdown = false;
            }
        },

        deletePost() {
            Swal.fire({
                icon: 'warning',
                title: 'Deseja mesmo excluir o post?',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir!',
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        await axios.delete(`/posts/${this.post.id}`);
                        Swal.fire({
                            icon: 'success',
                            title: 'Post excluído com sucesso!',
                            showConfirmButton: false,
                            timer: 1500,
                        });
                        // Redireciona ou atualiza a lista de posts
                        window.location.href = '/';
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro ao excluir o post!',
                            text: error.response?.data.message || 'Ocorreu um erro inesperado.',
                        });
                    }
                }
            });
            this.showDropdown = false; // Fecha o dropdown após a exclusão
        },

        // Inicializar ou atualizar o Quill editor
        initializeQuillEditor() {
            this.$nextTick(() => {
                if (this.quillEditor) {
                    this.quillEditor.root.innerHTML = ''; // Limpa o conteúdo do editor existente
                    this.quillEditor = null; // Destrói a instância do editor
                }

                // Inicializa o editor Quill
                this.quillEditor = new Quill(this.$refs.quillEditor, {
                    theme: 'snow',
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

                // Atualiza o conteúdo do Quill com a descrição do post
                if (this.quillEditor && this.postToEdit.description) {
                    this.quillEditor.root.innerHTML = this.postToEdit.description;
                }
            });
        },

        closeEditModal() {
            // Limpa o conteúdo do Quill e destrói a instância
            if (this.quillEditor) {
                this.quillEditor.root.innerHTML = '';
                this.quillEditor = null;
            }
            this.showEditModal = false;
        },

        validateForm() {
            this.errors = {};
            if (!this.postToEdit.title.trim()) {
                this.errors.title = 'Você precisa dar um título ao seu post.';
            }
            if (!this.postToEdit.description.trim()) {
                this.errors.description = 'Você precisa dar uma descrição ao seu post.';
            }
            if (!this.postToEdit.tags.length) {
                this.errors.tags = 'Defina pelo menos uma tag ao seu post.';
            }
            return Object.keys(this.errors).length === 0;
        },

        async updatePost() {
            if (!this.validateForm()) return;

            this.postToEdit.description = this.quillEditor.root.innerHTML;
            const tagsToSend = this.postToEdit.tags.map((tag) => tag.id);

            try {
                await axios.put(`/posts/${this.post.id}`, {
                    ...this.postToEdit,
                    tags: tagsToSend,
                });

                Swal.fire({
                    icon: 'success',
                    title: 'Seu Post foi atualizado com sucesso!',
                    showConfirmButton: false,
                    timer: 1500,
                });

                if (this.quillEditor) {
                    this.quillEditor.root.innerHTML = this.postToEdit.description;
                }

                this.showEditModal = false;
                this.fetchPost(); // Atualiza os dados do post na tela
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro ao atualizar o post!',
                    text: error.response?.data.message || 'Ocorreu um erro inesperado.',
                });
            }
        },

        openTaggingModal() {
            this.showTaggingModal = true;
        },

        closeTaggingModal() {
            this.showTaggingModal = false;
        },

        setTags(tags) {
            this.postToEdit.tags = tags.map((tag) => ({
                id: parseInt(tag.id, 10),
                name: tag.name || '',
                code: tag.code || null,
                color: tag.color || '#ccc', // Cor padrão caso não seja definida
            }));
        },
    },
    mounted() {
        this.fetchPost();
        document.addEventListener('click', this.closeDropdown);
    },
    beforeUnmount() {
        // Remover o ouvinte de evento quando o componente for destruído
        document.removeEventListener('click', this.closeDropdown);
  },
    watch: {
        showEditModal(newVal) {
            if (newVal) {
                this.initializeQuillEditor();
            }
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

.cursor-pointer {
    cursor: pointer;
}
</style>
