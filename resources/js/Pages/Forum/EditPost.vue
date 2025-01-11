<template>
    <div class="container mx-auto p-6 bg-white min-h-screen">
        <!-- Cabeçalho do Post -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center">
                <img class="w-12 h-12 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                <p class="ml-2">
                    {{ post?.user?.name }}
                    <button
                        class="text-blue-600 cursor-pointer hover:underline hover:-translate-y-1 transition-transform transform"
                        v-tooltip.focus.top="formatDate(post?.created_at)">
                        {{ formatRelativeTime(post?.created_at) }}
                    </button>
                </p>


            </div>
        </div>

        <!-- Título do Post -->
        <h2 class="text-3xl font-bold text-gray-900 mb-4">{{ post?.title }}</h2>

        <!-- Descrição do Post -->
        <div class="text-lg text-gray-700 mb-6">
            <p>{{ post?.description }}</p>
        </div>

        <!-- Informações do Post -->
        <div class="space-y-4 mb-6">
            <div>
                <span class="font-semibold">Tags: </span>
                <span v-for="(tag, index) in post?.tags" :key="index" :style="{ backgroundColor: tag.color }"
                    class="items-center text-white px-2 py-1 rounded-md text-sm mr-2" style="text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6);">
                    <i :class="tag.icon" class="text-sm text-white"></i> {{ tag.name }}
                </span>
            </div>
        </div>

        <!-- Botão para Editar Post -->
        <div class="mt-6">
            <button @click="editPost" class="px-6 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                Editar Post
            </button>
        </div>
    </div>

    <!-- Modal de Edição de Post -->
    <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
        <div class="bg-white rounded shadow p-6 w-full max-w-lg text-black">
            <h2 class="text-xl font-semibold mb-4">Editar Post</h2>
            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700">Título</label>
                <input v-model="postToEdit.title" id="title"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :class="{ 'border-red-500': errors.title }" required />
                <p v-if="errors.title" class="text-red-500 text-sm mt-1">{{ errors.title }}</p>
            </div>
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700">Descrição</label>
                <textarea v-model="postToEdit.description" id="description" rows="4"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :class="{ 'border-red-500': errors.description }" required></textarea>
                <p v-if="errors.description" class="text-red-500 text-sm mt-1">{{ errors.description }}</p>
            </div>

            <!-- Botão para Escolher Tags -->
            <div class="mb-4">
                <button @click="openTaggingModal" class="py-2 px-4 bg-gray-500 text-white rounded hover:bg-gray-600">
                    Escolha as Tags
                </button>
            </div>

            <div v-if="postToEdit.tags.length > 0" class="mb-4">
                <h3 class="font-medium text-gray-700">Tags Selecionadas:</h3>
                <span class="list-disc list-inside">
                    <span v-for="(tag, index) in postToEdit.tags" :key="index"
                        class="flex items-center text-black space-x-2">
                        <span class="w-4 h-4 rounded-full" :style="{ backgroundColor: tag.color }"></span>
                        <span>{{ tag?.name }}</span>
                    </span>
                </span>
            </div>

            <p v-if="errors.tags" class="text-red-500 text-sm mt-1">{{ errors.tags }}</p>

            <div class="flex justify-end space-x-4">
                <button @click="closeEditModal" class="py-2 px-4 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
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
</template>

<script>
import Tooltip from 'primevue/tooltip';
import TaggingModal from './TaggingModal.vue';
import Swal from 'sweetalert2';
import axios from 'axios';
import moment from 'moment';
import 'moment/dist/locale/pt-br'

export default {
    components: { TaggingModal, Tooltip },
    props: {
        posts: Object, // Recebe posts do Laravel/Inertia
    },
    data() {
        return {
            post: null,
            postToEdit: { title: '', description: '', tags: [] },
            showEditModal: false,
            showTaggingModal: false,
            errors: {},
            formattedDate: '',
            showTooltip: false,
        };
    },
    methods: {
        // Função para carregar o post
        async fetchPost() {
            try {
                const response = await axios.get(`/posts/${window.location.pathname.split('/')[2]}`);
                this.post = response.data;
                this.formattedDate = moment(this.post.created_at).format('DD/MM/YYYY');
            } catch (error) {
                console.error('Erro ao carregar o post:', error);
                this.post = null; // Garante que post é nulo em caso de erro
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
            this.postToEdit = { ...this.post }; // Preenche os dados do post para edição
            this.showEditModal = true;
        },

        closeEditModal() {
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

        // Função para atualizar o post
        async updatePost() {
            if (!this.validateForm()) return;

            // Ajuste das tags para enviar apenas os IDs
            const tagsToSend = this.postToEdit.tags.map(tag => tag.id);

            try {
                // Envia os dados para o backend
                await axios.put(`/posts/${this.post.id}`, {
                    ...this.postToEdit,
                    tags: tagsToSend, // Estou (Mó saco!) enviando apenas os IDs das tags
                });

                Swal.fire({
                    icon: 'success',
                    title: 'Seu Post foi atualizado com sucesso!',
                    showConfirmButton: false,
                    timer: 1500,
                });

                this.showEditModal = false;
                this.fetchPost(); // Atualiza os dados do post na tela :D
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro ao atualizar o post!',
                    text: error.response?.data.message || 'Ocorreu um erro inesperado.',
                });
            }
        },

        // Função para abrir o modal de tags
        openTaggingModal() {
            this.showTaggingModal = true;
        },

        closeTaggingModal() {
            this.showTaggingModal = false;
        },

        // Função para setar as tags
        setTags(tags) {
            this.postToEdit.tags = tags.map(tag => ({
                id: parseInt(tag.id, 10),
                name: tag.name || '',
                code: tag.code || null,
                color: tag.color || '#ccc', // Cor padrão caso não seja definida
            }));
        },
    },
    mounted() {
        this.fetchPost(); // Carregar o post ao montar o componente
    },
};
</script>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}

.container {
    max-width: 100%;
    padding: 20px;
}

body {
    background-color: #f3f4f6;
}

.text-lg {
    line-height: 1.6;
}

h2 {
    margin-bottom: 1rem;
}
</style>
