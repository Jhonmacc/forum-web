<!-- resources/js/Pages/Forum/Index.vue -->
<script setup>
import Header from '@/Components/Header.vue';
</script>

<template>
    <div class="bg-neutral-100 min-h-screen">
        <Header />

        <!-- Conteúdo Principal -->
        <div class="flex flex-1 pl-4 pr-4 md:pl-40 md:pr-40">
            <!-- Menu Lateral -->
            <div class="w-full md:w-1/4 container p-6 list-none space-y-2">
                <button @click="openCreatePostModal"
                    class="w-full py-2 px-4 mb-4 bg-yellow-400 text-white rounded-3xl hover:bg-yellow-500 shadow-md hover:-translate-y-1 transition-transform">
                    + Criar Post
                </button>
                <h2 class="text-xl font-bold text-gray-700 mb-4">Filtro por grupos</h2>
                <li @click="filterPosts('Todos')"
                    class="w-full py-2 px-4 mb-4 text-yellow-700 font-medium cursor-pointer hover:text-yellow-300 hover:-translate-y-1 transition-transform">
                    Todas as Categorias
                </li>
                <li @click="filterPosts('Suporte')"
                    class="w-full py-2 px-4 mb-4 text-yellow-700 font-medium cursor-pointer hover:text-yellow-300 hover:-translate-y-1 transition-transform">
                    Suporte
                </li>
                <li @click="filterPosts('Sugestão')"
                    class="w-full py-2 px-4 mb-4 text-yellow-700 font-medium cursor-pointer hover:text-yellow-300 hover:-translate-y-1 transition-transform">
                    Sugestão
                </li>
            </div>

            <!-- Timeline -->
            <ul class="flex-1 p-6" role="feed">
                <!-- Lista de Posts -->
                <li v-for="post in posts.data" :key="post.id" @click="$inertia.get(`/posts/${post.id}/edit`)"
                    class="cursor-pointer mb-6 py-4 px-6 hover:bg-yellow-50 hover:-translate-y-1 rounded-3xl transition-shadow">
                    <div class="container mx-auto">
                        <!-- Título e Tags/Comentários/Likes -->
                        <div class="flex justify-between items-start mb-4">
                            <h2 class="text-xl font-semibold text-gray-800">
                                {{ post.title.length > 50 ? post.title.slice(0, 50) + '...' : post.title }}
                            </h2>
                            <div class="flex items-center space-x-2">
                                <!-- Tags -->
                                <span v-for="tag in post.tags" :key="tag.id" :style="{ backgroundColor: tag.color }"
                                    class="inline-block px-2 py-1 text-sm text-white rounded"
                                    style="text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6);">
                                    <i :class="tag.icon" class="text-sm text-white"></i>
                                    {{ tag.name }}
                                </span>
                                <!-- Contagem de Comentários -->
                                <div class="flex items-center">
                                    <i class="fa-solid fa-comment text-gray-400"></i>
                                    <span class="text-sm text-gray-400 pl-1">{{ post.comments_count || 0 }}</span>
                                </div>
                                <!-- Contagem de Likes -->
                                <div class="flex items-center">
                                    <i class="fa-solid fa-heart text-gray-400"></i>
                                    <span class="text-sm text-gray-400 pl-1">{{ post.likes_count || 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <!-- Descrição -->
                        <p class="text-sm text-gray-600 description-preview mb-4" v-html="getDescriptionPreview(post.description)"></p>
                        <!-- Autor -->
                        <p class="text-sm text-gray-500">
                            Autor: {{ post.user.name }} {{ formatRelativeTime(post.created_at) }}
                        </p>
                    </div>
                </li>
            </ul>
        </div>
        <!-- Modal de Criação de Post -->
        <create-post-modal v-if="showCreatePostModal" @close="closeCreatePostModal" @success="refreshPosts" />
    </div>
</template>

<script>
import Button from 'primevue/button';
import Badge from 'primevue/badge';
import OverlayBadge from 'primevue/overlaybadge';
import CreatePostModal from './CreatePostModal.vue';
import moment from 'moment';
import 'moment/dist/locale/pt-br';

export default {
    components: { CreatePostModal, Badge, OverlayBadge, Button },
    props: {
        posts: Object, // Recebe posts do Laravel/Inertia
    },
    data() {
        return {
            showCreatePostModal: false,
            activeMenu: 'forum', // Marca o Fórum como o ativo por padrão
            isSearchExpanded: false, // Estado para controlar se a pesquisa está expandida
        };
    },
    methods: {
        setActiveMenu(menu) {
            this.activeMenu = menu;
        },
        filterPosts(tag) {
            this.$inertia
                .get(`/forum?tag=${tag}`)
                .then(response => {
                    if (response && response.data) {
                        this.posts = response.data;
                    } else {
                        console.error('A resposta da API não contém dados para a tag.');
                    }
                })
                .catch(error => {
                    console.error('Erro ao filtrar posts:', error);
                });
        },
        openCreatePostModal() {
            this.showCreatePostModal = true;
        },
        refreshPosts() {
            this.$inertia.get('/forum');
        },
        closeCreatePostModal() {
            this.showCreatePostModal = false;
        },
        formatRelativeTime(date) {
            return moment(date).fromNow(); // Retorna o tempo relativo
        },
        toggleSearch(event) {
            event.stopPropagation(); // Previne o fechamento ao clicar no campo de pesquisa
            this.isSearchExpanded = !this.isSearchExpanded; // Alterna o estado de expansão do campo de pesquisa
        },
        getDescriptionPreview(description) {
            const plainText = description.replace(/<[^>]+>/g, '');
            return plainText.length > 100 ? plainText.slice(0, 100) + '...' : plainText;
        },
    },
};
</script>

<style scoped>
.description-preview {
    display: -webkit-box;
    -webkit-line-clamp: 2; /* Limita a 2 linhas */
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.5em; /* Altura da linha para consistência */
    max-height: 3em; /* 2 linhas x 1.5em */
}

/* Estilização responsiva */
@media (max-width: 768px) {
    .flex-1 {
        padding-left: 1rem;
        padding-right: 1rem;
    }

    .w-1 {
        width: 100%;
        margin-bottom: 1rem;
    }

    .flex {
        flex-direction: column;
        align-items: flex-start;
    }

    .flex.items-start {
        flex-direction: column;
        align-items: flex-start;
    }

    .space-x-2 > * + * {
        margin-left: 0;
        margin-top: 0.5rem;
    }
}
</style>
