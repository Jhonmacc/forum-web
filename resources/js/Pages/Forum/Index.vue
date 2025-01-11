<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const logout = () => {
    router.post(route('logout'));
};

const isDropdownOpen = ref(false); // Estado para controlar a abertura/fechamento do dropdown

const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value; // Alterna o estado do dropdown
};

const closeDropdown = () => {
    isDropdownOpen.value = false; // Fecha o dropdown
};

const handleClickOutside = (event) => {
    if (!event.target.closest('.dropdown-container')) {
        closeDropdown(); // Fecha o dropdown se o clique for fora do dropdown
    }
};

</script>

<template>
    <div class="flex flex-col min-h-screen bg-gray-100" @click="handleClickOutside">
        <!-- Menu Superior -->
        <header class="w-full bg-white shadow-md p-4">
            <!-- Navegação -->
            <nav class="flex justify-center w-full space-x-4 items-center">
                <a href="/dashboard"
                    class="py-2 px-4 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300  hover:-translate-y-1 transition-transform"
                    :class="{ 'bg-indigo-500 text-white': activeMenu === 'dashboard' }"
                    @click="setActiveMenu('dashboard')">
                    Dashboard
                </a>
                <a href="/forum"
                    class="py-2 px-4 bg-gray-200 text-gray-700 rounded-lg hover:bg-yellow-500 hover:-translate-y-1 transition-transform"
                    :class="{ 'bg-yellow-400 text-white': activeMenu === 'forum' }" @click="setActiveMenu('forum')">
                    Fórum
                </a>
                <a href="/meus-posts"
                    class="py-2 px-4 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 hover:-translate-y-1 transition-transform"
                    :class="{ 'bg-indigo-500 text-white': activeMenu === 'meus-posts' }"
                    @click="setActiveMenu('meus-posts')">
                    Meus Posts
                </a>
            </nav>
        </header>

        <!-- Barra de Toogle e Pesquisa -->
        <div class="w-full bg-zinc-700 shadow-md p-4 mt-4 text-gray-700">
            <div class="flex items-center justify-end">
                <!-- Container para os campos de Pesquisa, Notificação e Perfil -->
                <div class="flex items-center space-x-6">
                    <!-- Campo de Pesquisa -->
                    <div ref="searchField"
                        class="relative flex items-center space-x-2 p-2 rounded-lg transition-all duration-500 ease-in-out"
                        :class="{ 'w-64': !isSearchExpanded, 'w-96': isSearchExpanded }" @click="toggleSearch($event)">
                        <i class="fa-solid fa-magnifying-glass text-gray-500 absolute left-7"></i>
                        <input type="text" placeholder="Pesquisar..."
                            class="w-full p-2 pl-10 rounded-2xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </div>

                    <!-- Campo de Notificação -->
                    <div class="relative ">
                        <div class="card flex justify-center flex-wrap gap-4">
                            <i class="fa-regular fa-bell"></i>
                        </div>
                    </div>

                    <!-- Acesso ao Perfil -->
                    <div class="relative dropdown-container"> <!-- Adicionei a classe para identificar o dropdown -->
                        <!-- Dropdown button -->
                        <button @click="toggleDropdown"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-full text-white hover:bg-neutral-500 focus:outline-none focus:bg-neutral-500 transition ease-in-out duration-150">
                            <!-- User avatar -->
                            <img class="size-8 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                            <!-- User name -->
                            <span class="ml-2 text-white">{{ $page.props.auth.user.name }}</span>
                            <!-- FontAwesome Icon -->
                            <i class="fas fa-chevron-down ml-2 text-sm"></i>
                        </button>

                        <!-- Dropdown menu -->
                        <div v-show="isDropdownOpen"
                            class="absolute right-0 mt-2 w-48 bg-white text-white rounded-md shadow-lg z-10"
                            @click.stop>
                            <div class="py-1">
                                <!-- Profile link -->
                                <a :href="route('profile.show')"
                                    class="block px-4 py-2 text-sm text-black hover:bg-yellow-100">
                                    <i class="fas fa-user mr-2"></i> Perfil
                                </a>
                                <!-- Logout link -->
                                <form @submit.prevent="logout" class="block">
                                    <button type="submit"
                                        class="w-full text-left px-4 py-2 text-black hover:bg-yellow-100">
                                        <i class="fas fa-sign-out-alt mr-2"></i> Sair
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
.post-title-container {
    justify-content: space-between;
}

.post-title {
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: calc(100% - 60px);
}

/* Estilo para transição suave */
.transition-all {
    transition: all 0.5s ease-in-out;
}
</style>
