<template>
    <div class="flex flex-col min-h-screen bg-gray-100">
        <!-- Menu Superior -->
        <header class="w-full bg-white shadow-md p-4">
            <!-- Navegação -->
            <nav class="flex justify-center w-full space-x-4 items-center">
                <a href="/dashboard"
                    class="py-2 px-4 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-transform transform hover:-translate-y-1"
                    :class="{ 'bg-indigo-500 text-white': activeMenu === 'dashboard' }"
                    @click="setActiveMenu('dashboard')">
                    Dashboard
                </a>
                <a href="/forum"
                    class="py-2 px-4 bg-gray-200 text-gray-700 rounded-lg hover:bg-yellow-500 transition-transform transform hover:-translate-y-1"
                    :class="{ 'bg-yellow-400 text-white': activeMenu === 'forum' }" @click="setActiveMenu('forum')">
                    Fórum
                </a>
                <a href="/meus-posts"
                    class="py-2 px-4 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-transform transform hover:-translate-y-1"
                    :class="{ 'bg-indigo-500 text-white': activeMenu === 'meus-posts' }"
                    @click="setActiveMenu('meus-posts')">
                    Meus Posts
                </a>
            </nav>
        </header>

        <!-- Barra de Toogle e Pesquisa -->
        <header class="w-full bg-white shadow-md p-4 mt-4 text-gray-700">
            <div class="flex items-center justify-end">
                <!-- Container para os campos de Pesquisa, Notificação e Perfil -->
                <div class="flex items-center space-x-6">
                    <!-- Campo de Pesquisa -->
                    <div ref="searchField"
                        class="relative flex items-center space-x-2 p-2 rounded-lg transition-all duration-500 ease-in-out"
                        :class="{ 'w-64': !isSearchExpanded, 'w-96': isSearchExpanded }" @click="toggleSearch($event)">
                        <i class="pi pi-search text-gray-500 absolute left-7"></i>
                        <input type="text" placeholder="Pesquisar..."
                            class="w-full p-2 pl-10 rounded-2xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </div>

                    <!-- Campo de Notificação -->
                    <div class="relative">
                        <div class="card flex justify-center flex-wrap gap-4">
                            <Button severity="warning" type="button" label="Noficiações" icon="pi pi-bell" badge="3" />
                        </div>
                    </div>

                    <!-- Acesso ao Perfil -->
                    <div class="flex items-center space-x-2">
                        <span class="text-gray-700">Olá, {{ userName }}</span>
                        <button class="flex items-center p-2 rounded-full hover:bg-gray-200">
                            <img :src="userAvatar" alt="Avatar" class="w-8 h-8 rounded-full" />
                        </button>
                    </div>

                </div>
            </div>
        </header>

        <!-- Conteúdo Principal -->
        <div class="flex flex-1 pl-40 pr-40">
            <!-- Menu Lateral -->
            <div class="w-1/4 container  p-6 list-none space-y-2">
                <!-- Botão Criar Post -->
                <button @click="openCreatePostModal"
                    class="w-full py-2 px-4 mb-4 bg-yellow-400 text-white rounded-3xl hover:bg-yellow-500 shadow-md transition-transform transform hover:-translate-y-1">
                    + Criar Post
                </button>
                <h2 class="text-xl font-bold text-gray-700 mb-4">Filtro por grupos</h2>
                <li @click="filterPosts('Todos')"
                    class="w-full py-2 px-4 mb-4 text-yellow-700 font-medium cursor-pointer hover:text-yellow-300  transform hover:-translate-y-1">
                    Todas as Categorias
                </li>
                <li @click="filterPosts('Suporte')"
                    class="w-full py-2 px-4 mb-4 text-yellow-700 font-medium cursor-pointer hover:text-yellow-300  transform hover:-translate-y-1">
                    Suporte
                </li>
                <li @click="filterPosts('Sugestão')"
                    class="w-full py-2 px-4 mb-4 text-yellow-700 font-medium cursor-pointer hover:text-yellow-300  transform hover:-translate-y-1">
                    Sugestão
                </li>
            </div>

            <!-- Timeline -->
            <ul class="flex-1 p-6" role="feed">
                <!-- Lista de Posts -->
                <div v-for="post in posts.data" :key="post.id"
                    class="mb-6 bg-white p-6 shadow-lg rounded-lg transition-transform transform hover:-translate-y-1 hover:shadow-xl">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800 mb-2">{{ post.title }}</h2>
                            <p class="text-sm text-gray-500">
                                Autor: {{ post.user.name }} há {{ formatRelativeTime(post.created_at) }}
                            </p>
                        </div>
                        <div class="flex">
                            <span v-for="tag in post.tags" :key="tag.id" :style="{ backgroundColor: tag.color }"
                                class="inline-block px-1 py-0 text-sm text-white first:rounded-l last:rounded-r"
                                style="text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6);">
                                {{ tag.name }}
                            </span>
                        </div>
                    </div>

                    <!-- Tags -->
                    <div class="mt-4">
                        <span class="text-sm text-gray-400">{{ post.comments_count || 0 }} comentários</span>
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
import 'primeicons/primeicons.css';
import CreatePostModal from './CreatePostModal.vue';
import moment from 'moment'; // Importa o moment.js
import 'moment/locale/pt-br'; // Importa o idioma português

moment.locale('pt-br'); // Define o idioma global para português

export default {
    components: { CreatePostModal, Badge, OverlayBadge, Button },
    props: {
        posts: Object, // Recebe posts do Laravel/Inertia
    },
    data() {
        return {
            showCreatePostModal: false,
            activeMenu: 'forum', // Marca o Fórum como o ativo por padrão
            userName: 'João',  // Exemplo de nome de usuário, você pode substituir pelo nome real
            userAvatar: 'https://via.placeholder.com/150', // Exemplo de avatar, você pode substituir pela URL do avatar real
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
            return moment(date).fromNow(); // Retorna o tempo relativo formatado em português
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
/* Estilo para transição suave */
.transition-all {
    transition: all 0.5s ease-in-out;
}
</style>
