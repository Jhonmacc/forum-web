<script setup>
import Header from '@/Components/Header.vue';
</script>

<template>
    <div class="bg-neutral-100 min-h-screen">
        <Header />

        <!-- Conteúdo Principal -->
        <div class="flex flex-1 pl-40 pr-40">
            <!-- Menu Lateral -->
            <div class="w-1/4 container  p-6 list-none space-y-2">
                <!-- Botão Criar Post -->
                <button @click="openCreatePostModal"
                    class="w-full py-2 px-4 mb-4 bg-yellow-400 text-white rounded-3xl hover:bg-yellow-500 shadow-md hover:-translate-y-1 transition-transform">
                    + Criar Post
                </button>
                <h2 class="text-xl font-bold text-gray-700 mb-4">Filtro por grupos</h2>
                <li @click="filterPosts('Todos')"
                    class="w-full py-2 px-4 mb-4 text-yellow-700 font-medium cursor-pointer hover:text-yellow-300   hover:-translate-y-1 transition-transform">
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
                <div v-for="post in posts.data" :key="post.id" @click="$inertia.get(`/posts/${post.id}/edit`)"
                    class="cursor-pointer mb-6 bg-white p-6 shadow-lg rounded-lg hover:-translate-y-1 transition-transform hover:shadow-xl">
                    <div class="flex justify-between items-start">
                        <div class="post-title-container">
                            <h2 class="text-xl font-semibold post-title text-gray-800 mb-2">
                                {{ post.title.length > 50 ? post.title.slice(0, 50) + '...' : post.title }}
                            </h2>
                            <p class="text-sm text-gray-500">
                                Autor: {{ post.user.name }} {{ formatRelativeTime(post.created_at) }}
                            </p>
                        </div>
                        <div class="flex">
                            <span v-for="tag in post.tags" :key="tag.id" :style="{ backgroundColor: tag.color }"
                                class="inline-block px-1 py-0 text-sm text-white first:rounded-l last:rounded-r"
                                style="text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6);">
                                <i :class="tag.icon" class="text-sm text-white"></i>
                                {{ tag.name }}
                            </span>
                            <i class="fa-solid fa-comment pl-2"></i>
                            <span class="text-sm text-gray-400 pl-2">{{ post.comments_count || 0 }} </span>
                        </div>
                    </div>
                </div>
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
import 'moment/dist/locale/pt-br'


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
        handleClickOutside(event) {
            if (!this.$refs.searchField.contains(event.target)) {
                this.isSearchExpanded = false; // Fecha a pesquisa se o clique for fora
            }
        },
    },
    mounted() {
        document.addEventListener('click', this.handleClickOutside); // Escuta os cliques fora
    },
    destroyed() {
        document.removeEventListener('click', this.handleClickOutside); // Remove o ouvinte quando o componente for destruído
    }
};
</script>

<style scoped>

</style>
