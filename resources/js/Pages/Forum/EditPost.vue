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
            <img class="w-16 h-16 cursor-pointer rounded-full object-cover
                hover:underline
                hover:-translate-y-1 transition-transform transform translate-y-4 -translate-x-2"
                :src="$page.props.post.user.profile_photo_url" :alt="$page.props.post.user.name">
            <div class="flex items-center space-x-2">
                <button class="hover:underline hover:-translate-y-1 transition-transform transform">{{
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
                        <path
                            d="M10 3a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 7a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 7a1.5 1.5 0 110-3 1.5 1.5 0 010 3z" />
                    </svg>
                </button>

                <!-- Dropdown menu -->
                <div v-if="showDropdown"
                    class="absolute top-full mt-2 w-32 bg-white border border-gray-300 rounded-lg shadow-lg z-10">
                    <button @click="editPost"
                        class="flex items-center w-full px-4 py-2 text-gray-700 hover:bg-gray-100">
                        <i class="fa-regular fa-pen-to-square mr-1"></i>
                        Editar
                    </button>
                    <button @click="deletePost"
                        class="flex items-center w-full px-4 py-2 text-gray-700 hover:bg-gray-100">
                        <i class="fa-regular fa-trash-can mr-1"></i>
                        Deletar
                    </button>
                </div>
            </div>
        </div>

        <!-- Comentários -->
        <div class="p-6 bg-gray-100 rounded-lg max-w-4xl mx-auto mt-8">
            <h3 class="text-lg font-semibold mb-4">Comentários</h3>

            <div v-if="localIsAuthenticated" class="mb-4">
                <textarea v-model="newComment" @input="detectMention" placeholder="Adicione um comentário..."
                    class="w-full p-2 border rounded-md focus:ring focus:ring-indigo-300 resize-none"
                    rows="3"></textarea>
                <ul v-if="showSuggestions" class="border rounded bg-white">
                    <li v-for="user in suggestions" :key="user.id" @click="addMention(user)"
                        class="p-2 cursor-pointer hover:bg-gray-100">
                        {{ user.name }}
                    </li>
                </ul>
                <div class="flex justify-end mt-2">
                    <button @click="addComment" class="mt-2 px-4 py-2 bg-blue-500 text-white rounded">Comentar</button>
                </div>
            </div>

            <div v-else class="text-gray-700 mb-4">
                <p>Você precisa estar logado para comentar.</p>
            </div>

            <div v-if="post && post.comments && post.comments.length > 0" class="space-y-4">
                <!-- Iteração sobre os comentários -->
                <div v-for="(comment, index) in post.comments" :key="comment.id" class="p-4 bg-white shadow rounded">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <button class="hover:underline hover:-translate-y-1 transition-transform transform mr-2">
                                {{ comment.user.name }}
                            </button>
                            <button
                                class="text-blue-600 text-sm cursor-pointer hover:underline hover:-translate-y-1 transition-transform transform mr-2"
                                v-tooltip.focus.top="formatDate(comment?.created_at)">
                                {{ formatRelativeTime(comment?.created_at) }}
                            </button>
                            <p v-if="comment.liked" class="text-xs text-gray-500 ml-2 mb-0">Você curtiu esse comentário.
                            </p>
                            <!-- Curtidas e respostas ao comentário -->
                            <div class="flex items-center space-x-2">
                                <button @click="toggleLike(comment)"
                                    class="focus:outline-none flex items-center space-x-1">
                                    <i :class="{
                                        'fa-regular fa-heart': !comment.liked,
                                        'fa-solid fa-heart text-red-500': comment.liked
                                    }" class="transition duration-200 ease-in-out text-lg"></i>
                                    <span class="text-sm text-gray-600">{{ comment.likes_count }}</span>
                                </button>
                                <div>
                                    <p v-html="renderComment(comment.content)"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botão para responder o comentário -->
                    <div>
                        <button @click="openReplyModal(comment)" class="text-blue-500 text-sm hover:underline">
                            Responder
                        </button>
                        <!-- Modal de resposta para o comentário -->
                        <div v-if="activeReplyCommentId === comment.id" class="mt-4">
                            <textarea v-model="replyBody" placeholder="Escreva sua resposta..."
                                @input="detectReplyMention($event, 'replyToComment')"
                                class="w-full p-2 border rounded-md focus:ring focus:ring-indigo-300 resize-none"
                                rows="3"></textarea>
                            <ul v-if="showReplySuggestions" class="border rounded bg-white">
                                <li v-for="user in replySuggestions" :key="user.id"
                                    @click="addReplyMention(user, 'replyToComment')"
                                    class="p-2 cursor-pointer hover:bg-gray-100">
                                    {{ user.name }}
                                </li>
                            </ul>
                            <div class="flex justify-end mt-2">
                                <button @click="replyToComment(comment.id)"
                                    class="px-4 py-2 bg-blue-500 text-white rounded">
                                    Enviar Resposta
                                </button>
                                <button @click="closeReplyModal" class="px-4 py-2 bg-gray-300 text-black rounded ml-2">
                                    Cancelar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Respostas do comentário -->
                    <div v-if="comment.replies && comment.replies.length" class="mt-4">
                        <div v-for="(reply, rIndex) in getVisibleReplies(comment)" :key="reply?.id"
                            class="ml-4 pl-4 border-l">
                            <div class="flex items-center justify-between mb-1">
                                <span v-if="reply?.user?.name" class="font-bold">{{ reply.user.name }}</span>
                                <span class="text-sm text-gray-500">{{ formatRelativeTime(reply?.created_at) }}</span>
                            </div>
                            <p v-if="reply?.body" class="text-gray-600">{{ reply.body }}</p>

                            <!-- Botão para responder uma resposta -->
                            <button @click="openReplyToReplyModal(reply)" class="text-blue-500 text-sm hover:underline">
                                Responder
                            </button>

                            <!-- Modal de resposta para uma resposta -->
                            <div v-if="activeReplyId === reply.id" class="mt-4">
                                <textarea v-model="replyToReplyBody" placeholder="Escreva sua resposta..."
                                    @input="detectReplyMention($event, 'replyToReply')"
                                    class="w-full p-2 border rounded-md focus:ring focus:ring-indigo-300 resize-none"
                                    rows="3"></textarea>
                                <ul v-if="showReplySuggestions" class="border rounded bg-white">
                                    <li v-for="user in replySuggestions" :key="user.id"
                                        @click="addReplyMention(user, 'replyToReply')"
                                        class="p-2 cursor-pointer hover:bg-gray-100">
                                        {{ user.name }}
                                    </li>
                                </ul>
                                <div class="flex justify-end mt-2">
                                    <button @click="replyToReply(reply)"
                                        class="px-4 py-2 bg-blue-500 text-white rounded">
                                        Enviar Resposta
                                    </button>
                                    <button @click="closeReplyToReplyModal"
                                        class="px-4 py-2 bg-gray-300 text-black rounded ml-2">
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Botão para ver mais/ocultar respostas -->
                        <div v-if="comment.replies.length > 3" class="mt-2">
                            <button v-if="!expandedComments.includes(comment.id)" @click="expandReplies(comment)"
                                class="text-blue-500 text-sm hover:underline">
                                Ver mais respostas
                            </button>
                            <button v-else @click="collapseReplies(comment)"
                                class="text-blue-500 text-sm hover:underline">
                                Ocultar respostas
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="text-gray-500">Nenhum comentário ainda. Seja o primeiro a comentar!</div>
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
            localIsAuthenticated: this.$page.props.auth.user !== null, // Renomeado para evitar conflito
            post: null,
            postToEdit: { title: '', description: '', tags: [] },
            quillEditor: null,
            showEditModal: false,
            showTaggingModal: false,
            errors: {},
            formattedDate: '',
            showTooltip: false,
            showDropdown: false, // Controla a exibição do dropdown
            newComment: '',
            content: '',
            suggestions: [],
            showSuggestions: false,
            mentions: [],
            comments: [],
            showReplyModal: false,
            selectedComment: null,
            replyBody: '',
            activeReplyCommentId: null, // ID do comentário cujo modal de resposta está ativo
            expandedComments: [], // Armazena os comentários expandidos
            activeReplyId: null, // ID da resposta sendo respondida
            replyToReplyBody: '', // Corpo da resposta para outra resposta
            replySuggestions: [],
            showReplySuggestions: false,
        };
    },
    computed: {
        canEditOrDeleteComment() {
            // Lógica para verificar se o usuário pode editar ou excluir o comentário
            return (comment) => this.$page.props.auth.user.id === comment.user_id; // Passa o comentário como argumento
        }
    },
    methods: {
        getVisibleReplies(comment) {
            // Retorna até 3 respostas se o comentário não estiver expandido
            if (!this.expandedComments.includes(comment.id)) {
                return (comment.replies || []).slice(0, 3);
            }

            // Retorna todas as respostas se o comentário estiver expandido
            return comment.replies || [];
        },


        expandReplies(comment) {
            // Adiciona o ID do comentário à lista de comentários expandidos
            this.expandedComments.push(comment.id);
        },

        collapseReplies(comment) {
            // Remove o ID do comentário da lista de comentários expandidos
            this.expandedComments = this.expandedComments.filter(id => id !== comment.id);
        },

        formatRelativeTime(date) {
            // Formata a data relativa usando moment.js
            return moment(date).fromNow();
        },
        async toggleLike(comment) {
            try {
                const response = await axios.post(`/comments/${comment.id}/like`);

                // Atualiza os dados do comentário no frontend
                comment.liked = response.data.liked; // Define se o comentário foi curtido
                comment.likes_count = response.data.likes_count; // Atualiza a contagem de curtidas
            } catch (error) {
                console.error('Erro ao curtir o comentário:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Ocorreu um erro ao tentar curtir o comentário.',
                });
            }
        },
        openReplyModal(comment) {
            this.activeReplyCommentId = comment.id; // Define o ID do comentário ativo
            this.replyBody = comment ? `@${comment.user.name} ` : '';// Pré-preenche o textarea com a menção ao autor
        },

        closeReplyModal() {
            this.activeReplyCommentId = null; // Fecha o modal de resposta
            this.replyBody = ''; // Limpa o campo de texto
        },

        openReplyToReplyModal(reply) {
            this.activeReplyId = reply.id;
            this.replyToReplyBody = `@${reply.user.name} `; // Pré-preenche com a menção
        },

        closeReplyToReplyModal() {
            this.activeReplyId = null;
            this.replyToReplyBody = '';
        },


        async replyToComment(commentId) {
            try {
                // Envia a resposta ao back-end
                await axios.post(`/comments/${commentId}/reply`, {
                    body: this.replyBody,
                });

                // Busca novamente o comentário atualizado
                const updatedComment = await axios.get(`/comments/${commentId}`);

                // Atualiza o comentário no array
                const commentIndex = this.post.comments.findIndex(c => c.id === commentId);
                if (commentIndex !== -1) {
                    // Substitui o comentário diretamente
                    this.post.comments[commentIndex] = updatedComment.data;
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Resposta enviada com sucesso!',
                    showConfirmButton: false,
                    timer: 1500,
                });

                this.closeReplyModal();
            } catch (error) {
                console.error('Erro ao enviar resposta:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Não foi possível enviar sua resposta.',
                });
            }
        },



        async replyToReply(reply) {
            try {
                // Envia a resposta ao back-end
                const response = await axios.post(`/replies/${reply.id}/reply`, {
                    body: this.replyToReplyBody,
                });

                // Nova resposta retornada pela API
                const newReply = response.data;

                // Atualiza o front-end
                const parentReplyIndex = this.post.comments.findIndex(
                    comment => comment.id === reply.comment_id
                );

                if (parentReplyIndex !== -1) {
                    const replies = this.post.comments[parentReplyIndex].replies;

                    // Adiciona a nova resposta ao array de respostas de forma reativa
                    if (Array.isArray(replies)) {
                        replies.push(newReply);
                    } else {
                        this.post.comments[parentReplyIndex].replies = [newReply];
                    }
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Resposta enviada com sucesso!',
                    showConfirmButton: false,
                    timer: 1500,
                });

                this.closeReplyToReplyModal();
                this.replyToReplyBody = ""; // Limpa o conteúdo do campo de resposta
            } catch (error) {
                console.error('Erro ao enviar a resposta:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Não foi possível enviar sua resposta.',
                });
            }
        },

        async detectMention(e) {
            const input = e.target.value;
            const mentionIndex = input.lastIndexOf('@');
            if (mentionIndex !== -1) {
                const query = input.slice(mentionIndex + 1);
                if (query.trim().length > 0) {
                    const { data } = await axios.get(`/users/search?query=${query}`);
                    this.suggestions = data;
                    this.showSuggestions = true;
                } else {
                    this.showSuggestions = false;
                }
            } else {
                this.showSuggestions = false;
            }
        },
        addMention(user) {
            const input = this.newComment;
            const mentionIndex = input.lastIndexOf('@');

            // Substitui o texto digitado após o '@' pelo nome do usuário mencionado
            this.newComment = input.slice(0, mentionIndex) + `@${user.name} `;
            this.mentions.push(user);
            // Esconde as sugestões
            this.showSuggestions = false;
        },
        renderComment(newComment) {
            return newComment.replace(/@([\w\s]+)/g, '<span class="text-blue-500">@$1</span>');
        },
        renderReply(replyContent) {
            return replyContent.replace(/@([\w\s]+)/g, '<span class="text-blue-500">@$1</span>');
        },

        async detectReplyMention(e, type) {
            const input = e.target.value;
            const mentionIndex = input.lastIndexOf('@');
            if (mentionIndex !== -1) {
                const query = input.slice(mentionIndex + 1);
                if (query.trim().length > 0) {
                    const { data } = await axios.get(`/users/search?query=${query}`);
                    if (type === 'replyToComment') {
                        this.replySuggestions = data;
                        this.showReplySuggestions = true;
                    } else if (type === 'replyToReply') {
                        this.replySuggestions = data;
                        this.showReplySuggestions = true;
                    }
                } else {
                    this.showReplySuggestions = false;
                }
            } else {
                this.showReplySuggestions = false;
            }
        },
        addReplyMention(user, type) {
            const input = type === 'replyToComment' ? this.replyBody : this.replyToReplyBody;
            const mentionIndex = input.lastIndexOf('@');

            if (type === 'replyToComment') {
                this.replyBody = input.slice(0, mentionIndex) + `@${user.name} `;
            } else if (type === 'replyToReply') {
                this.replyToReplyBody = input.slice(0, mentionIndex) + `@${user.name} `;
            }
            this.mentions.push(user);
            this.showReplySuggestions = false;
        },



        async fetchPost() {
            try {
                const response = await axios.get(`/posts/${window.location.pathname.split('/')[2]}`);
                this.post = response.data;

                // Adiciona propriedades de controle para curtidas e respostas
                this.post.comments = this.post.comments.map(comment => ({
                    ...comment,
                    liked: comment.likes.some(like => like.user_id === this.$page.props.auth.user.id),
                    likes_count: comment.likes.length,
                    replies: comment.replies.map(reply => ({
                        ...reply,
                        user: reply.user || null, // Garante que o usuário existe na resposta
                        children: reply.children || [], // Garante que as respostas-filhas sejam carregadas
                    })),
                }));

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

        editPost() {
            this.postToEdit = { ...this.post };
            this.showEditModal = true;
            this.showDropdown = false; // Fecha o dropdown ao abrir o modal
        },

        toggleDropdown() {
            this.showDropdown = !this.showDropdown;
        },

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
                            timer: 3000,
                        });
                        setTimeout(() => {
                            window.location.href = '/forum';
                        }, 1500);
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

        async addComment() {
            if (!this.newComment.trim()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Comentário vazio!',
                    text: 'Por favor, adicione um comentário antes de enviar.',
                });
                return;
            }

            try {
                const response = await axios.post(`/posts/${this.post.id}/comments`, {
                    content: this.newComment, // Usar o campo correto
                    post_id: this.post.id,
                    mentions: this.mentions,  // Envia as menções para o backend
                });

                if (response.data.message === 'Comentário adicionado com sucesso') {
                    this.post.comments.push(response.data.data);
                    Swal.fire({
                        icon: 'success',
                        title: 'Comentário adicionado com sucesso!',
                        showConfirmButton: false,
                        timer: 1500,
                    });
                } else {
                    throw new Error('Erro ao adicionar comentário');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro ao adicionar comentário!',
                    text: error.response?.data.message || 'Ocorreu um erro inesperado.',
                });
            }
        },

        async deleteComment(commentId) {
            Swal.fire({
                icon: 'warning',
                title: 'Deseja excluir este comentário?',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir!',
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        await axios.delete(`/comments/${commentId}`);
                        this.post.comments = this.post.comments.filter(c => c.id !== commentId);
                        Swal.fire({
                            icon: 'success',
                            title: 'Comentário excluído com sucesso!',
                            showConfirmButton: false,
                            timer: 3000,
                        });
                    } catch (error) {
                        console.error('Erro ao excluir comentário:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro ao excluir comentário!',
                            text: error.response?.data.message || 'Ocorreu um erro inesperado.',
                        });
                    }
                }
            });
        },

        async likeComment(commentId) {
            await axios.post(`/comments/${commentId}/like`);
            Swal.fire({
                icon: 'success',
                title: 'Você curtiu o comentário!',
                showConfirmButton: false,
                timer: 1500,
            });
        },


        initializeQuillEditor() {
            this.$nextTick(() => {
                if (this.quillEditor) {
                    this.quillEditor.root.innerHTML = '';
                    this.quillEditor = null;
                }
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
                if (this.quillEditor && this.postToEdit.description) {
                    this.quillEditor.root.innerHTML = this.postToEdit.description;
                }
            });
        },

        closeEditModal() {
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
                color: tag.color || '#ccc',
            }));
        },
    },
    mounted() {
        this.fetchPost();
        document.addEventListener('click', this.closeDropdown);
    },
    beforeUnmount() {
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
