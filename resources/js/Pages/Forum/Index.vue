<!-- resources/js/Pages/Forum/Index.vue -->
<template>
    <div class="bg-neutral-100 min-h-screen">
        <Header />

        <!-- Faixa no topo (exibida apenas se houver um filtro ativo diferente de 'Todos') -->
        <div v-if="activeFilter !== 'Todos' && activeTag"
             class="w-full flex flex-col items-center justify-center text-center py-4 px-6 mb-6 text-white"
             :style="{ backgroundColor: activeTag.color }">
            <div class="flex items-center space-x-2">
                <i :class="activeTag.icon" class="text-3xl text-white"></i>
                <h2 class="text-3xl font-bold text-white">{{ activeTag.name }}</h2>
            </div>
            <p class="mt-2 text-md text-white">{{ activeTag.description || 'Nenhuma descrição disponível.' }}</p>
        </div>

        <!-- Conteúdo Principal -->
        <div class="flex flex-1 pl-4 pr-4 md:pl-40 md:pr-40">
            <!-- Menu Lateral -->
            <div class="w-full md:w-1/4 container p-6 list-none space-y-2">
                <button @click="openCreatePostModal"
                        class="w-full py-2 px-4 mb-4 bg-yellow-400 text-white rounded-3xl hover:bg-yellow-500 shadow-md hover:-translate-y-1 transition-transform font-bold">
                    + Nova Discussão
                </button>
                <h2 class="text-xl font-bold text-gray-700 mb-4">Filtro por Grupos</h2>
                <li @click="filterPosts('Todos')"
                    :class="{ 'font-bold': activeFilter === 'Todos', 'text-brown-light': activeFilter !== 'Todos' }"
                    class="w-full py-2 px-4 mb-4 font-medium cursor-pointer hover:text-brown-lighter hover:-translate-y-1 transition-transform rounded-lg flex items-center space-x-2">
                    <i class="fa-solid fa-list"
                       :class="{ 'text-yellow-500': activeFilter === 'Todos', 'text-gray-500': activeFilter !== 'Todos' }"></i>
                    <span>Todas as Discussões</span>
                </li>
                <li @click="filterPosts('Suporte')"
                    :class="{ 'text-yellow-900 font-bold': activeFilter === 'Suporte', 'text-brown-light': activeFilter !== 'Suporte' }"
                    :style="activeFilter === 'Suporte' && activeTag ? { color: activeTag.color } : {}"
                    class="w-full py-2 px-4 mb-4 font-medium cursor-pointer hover:text-brown-lighter hover:-translate-y-1 transition-transform rounded-lg flex items-center space-x-2">
                    <i v-if="getTagByName('Suporte')" :class="getTagByName('Suporte').icon" class="text-gray-500"></i>
                    <span>Suporte</span>
                </li>
                <li @click="filterPosts('Ideias')"
                    :class="{ 'text-yellow-900 font-bold': activeFilter === 'Ideias', 'text-brown-light': activeFilter !== 'Ideias' }"
                    :style="activeFilter === 'Ideias' && activeTag ? { color: activeTag.color } : {}"
                    class="w-full py-2 px-4 mb-4 font-medium cursor-pointer hover:text-brown-lighter hover:-translate-y-1 transition-transform rounded-lg flex items-center space-x-2">
                    <i v-if="getTagByName('Ideias')" :class="getTagByName('Ideias').icon" class="text-gray-500"></i>
                    <span>Ideias</span>
                </li>
                <li @click="filterPosts('Artigo')"
                    :class="{ 'text-yellow-900 font-bold': activeFilter === 'Artigo', 'text-brown-light': activeFilter !== 'Artigo' }"
                    :style="activeFilter === 'Artigo' && activeTag ? { color: activeTag.color } : {}"
                    class="w-full py-2 px-4 mb-4 font-medium cursor-pointer hover:text-brown-lighter hover:-translate-y-1 transition-transform rounded-lg flex items-center space-x-2">
                    <i v-if="getTagByName('Artigo')" :class="getTagByName('Artigo').icon" class="text-gray-500"></i>
                    <span>Artigos</span>
                </li>
                <li @click="filterPosts('Bug')"
                    :class="{ 'text-yellow-900 font-bold': activeFilter === 'Bug', 'text-brown-light': activeFilter !== 'Bug' }"
                    :style="activeFilter === 'Bug' && activeTag ? { color: activeTag.color } : {}"
                    class="w-full py-2 px-4 mb-4 font-medium cursor-pointer hover:text-brown-lighter hover:-translate-y-1 transition-transform rounded-lg flex items-center space-x-2">
                    <i v-if="getTagByName('Bug')" :class="getTagByName('Bug').icon" class="text-gray-500"></i>
                    <span>Bugs</span>
                </li>
            </div>

            <!-- Timeline -->
            <div class="flex-1 p-12">
                <!-- Controles de Filtragem e Atualização -->
                <div class="flex justify-between items-center mb-6">
                    <!-- Dropdown de Filtragem -->
                    <div class="relative">
                        <button @click="toggleSortDropdown"
                                class="flex items-center space-x-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                            <span>{{ sortOption }}</span>
                            <i class="fa-solid fa-chevron-down"></i>
                        </button>
                        <ul v-if="showSortDropdown"
                            class="absolute mt-2 w-48 bg-white rounded-lg shadow-lg z-10">
                            <li @click="sortPosts('Últimas')"
                                class="px-4 py-2 text-gray-700 hover:bg-gray-100 cursor-pointer flex items-center space-x-2">
                                <i v-if="sortOption === 'Últimas'" class="fa-solid fa-check text-green-500"></i>
                                <span>Últimas</span>
                            </li>
                            <li @click="sortPosts('Mais novo')"
                                class="px-4 py-2 text-gray-700 hover:bg-gray-100 cursor-pointer flex items-center space-x-2">
                                <i v-if="sortOption === 'Mais novo'" class="fa-solid fa-check text-green-500"></i>
                                <span>Mais novo</span>
                            </li>
                            <li @click="sortPosts('Mais velho')"
                                class="px-4 py-2 text-gray-700 hover:bg-gray-100 cursor-pointer flex items-center space-x-2">
                                <i v-if="sortOption === 'Mais velho'" class="fa-solid fa-check text-green-500"></i>
                                <span>Mais velho</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Botão de Atualização -->
                    <button @click="refreshPostsWithTransition"
                            class="flex items-center justify-center w-10 h-10 bg-gray-200 rounded-full hover:bg-gray-300 transition-colors">
                        <i v-if="!isRefreshing" class="fa-solid fa-rotate text-gray-700"></i>
                        <i v-else class="fa-solid fa-spinner fa-spin text-gray-700"></i>
                    </button>
                </div>

                <!-- Spinner durante o carregamento -->
                <div v-if="isLoading && !loadedPosts.length" class="flex justify-center items-center h-64">
                    <svg class="animate-spin h-10 w-10 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <!-- Lista de Posts com transição -->
                <transition name="fade" mode="out-in">
                    <ul v-if="!isLoading || loadedPosts.length" key="posts-list" role="feed" class="post-list">
                        <li v-for="post in loadedPosts" :key="post.id" @click="router.get(`/posts/${post.id}/edit`)"
                            class="cursor-pointer mb-6 py-4 px-6 hover:bg-yellow-50 rounded-3xl transition-shadow">
                            <div class="container mx-auto">
                                <!-- Título e Tags/Comentários/Likes -->
                                <div class="flex justify-between items-start mb-4">
                                    <h2 class="text-xl font-semibold text-gray-800">
                                        {{ post.title.length > 50 ? post.title.slice(0, 50) + '...' : post.title }}
                                    </h2>
                                    <div class="flex items-center space-x-2">
                                        <!-- Tags -->
                                        <span v-for="tag in post.tags" :key="tag.id" :style="{ backgroundColor: tag.color }"
                                              class="inline-block px-2 py-1 text-sm text-white rounded font-bold tag-shadow">
                                            <i :class="tag.icon" class="text-sm text-white font-bold"></i>
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
                        <!-- Mensagem quando não há posts -->
                        <li v-if="!loadedPosts.length" class="text-center text-gray-500">
                            Nenhum post encontrado para este filtro.
                        </li>
                    </ul>
                </transition>

                <!-- Botão "Carregar Mais" -->
                <div v-if="hasMorePosts" class="flex justify-center mt-6">
                    <button @click="loadMorePosts"
                            class="flex items-center space-x-2 px-6 py-2 bg-yellow-400 text-white rounded-3xl hover:bg-yellow-500 transition-colors">
                        <span class="text-white font-bold">Carregar Mais</span>
                        <i v-if="isLoading" class="fa-solid fa-spinner fa-spin"></i>
                    </button>
                </div>
            </div>
        </div>
        <!-- Modal de Criação de Post -->
        <create-post-modal v-if="showCreatePostModal" @close="closeCreatePostModal" @success="refreshPosts" />
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import Header from '@/Components/Header.vue';
import CreatePostModal from './CreatePostModal.vue';
import moment from 'moment';
import 'moment/dist/locale/pt-br';
import { ref, computed } from 'vue';

// Props
const props = defineProps({
    posts: Object,
    filters: Object,
    tags: Array,
});

// Data
const showCreatePostModal = ref(false);
const activeMenu = ref('forum');
const activeFilter = ref(props.filters?.tag || 'Todos');
const sortOption = ref(props.filters?.sort || 'Últimas');
const currentPage = ref(1);
const perPage = ref(5); // Número fixo de posts por "página"
const isLoading = ref(false);
const showSortDropdown = ref(false);
const isRefreshing = ref(false);
const transitionTrigger = ref(0);
const loadedPosts = ref([]); // Lista acumulativa de posts
const totalPosts = ref(props.posts?.total || 0); // Total de posts disponíveis

// Computed
const activeTag = computed(() => {
    if (activeFilter.value === 'Todos' || !props.tags) return null;
    return props.tags.find(tag => tag.name === activeFilter.value) || null;
});

const hasMorePosts = computed(() => {
    return loadedPosts.value.length < totalPosts.value;
});

// Inicializar os posts carregados
loadedPosts.value = props.posts?.data || [];

// Methods
const getTagByName = (filterName) => {
    if (!props.tags) return null;
    return props.tags.find(tag => tag.name === filterName) || null;
};

const setActiveMenu = (menu) => {
    activeMenu.value = menu;
};

const fetchPosts = (reset = false) => {
    if (reset) {
        currentPage.value = 1;
        loadedPosts.value = [];
    }

    isLoading.value = true;
    router.get(`/forum`, {
        tag: activeFilter.value,
        sort: sortOption.value.toLowerCase(),
        page: currentPage.value,
        per_page: perPage.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            console.log('Requisição concluída com sucesso:', page);
            const newPosts = page.props.posts.data;
            totalPosts.value = page.props.posts.total;
            if (reset) {
                loadedPosts.value = newPosts;
            } else {
                loadedPosts.value = [...loadedPosts.value, ...newPosts];
            }
            isLoading.value = false;
            isRefreshing.value = false; // Garantir que o spinner pare
            transitionTrigger.value++;
        },
        onError: (error) => {
            console.error('Erro ao carregar posts:', error);
            isLoading.value = false;
            isRefreshing.value = false; // Garantir que o spinner pare em caso de erro
        },
        onFinish: () => {
            console.log('Requisição finalizada');
            isLoading.value = false;
            isRefreshing.value = false; // Garantir que o spinner pare
        },
    });
};

const filterPosts = (tag) => {
    activeFilter.value = tag;
    fetchPosts(true); // Resetar a lista ao mudar o filtro
};

const toggleSortDropdown = () => {
    showSortDropdown.value = !showSortDropdown.value;
};

const sortPosts = (option) => {
    sortOption.value = option;
    showSortDropdown.value = false;
    fetchPosts(true); // Resetar a lista ao mudar a ordenação
};

const loadMorePosts = () => {
    currentPage.value += 1;
    fetchPosts(); // Carregar mais posts sem resetar a lista
};

const refreshPostsWithTransition = () => {
    if (isRefreshing.value) return; // Evitar múltiplas requisições simultâneas
    isRefreshing.value = true;
    fetchPosts(true); // Resetar a lista ao atualizar
};

const openCreatePostModal = () => {
    showCreatePostModal.value = true;
};

const refreshPosts = () => {
    router.get('/forum');
};

const closeCreatePostModal = () => {
    showCreatePostModal.value = false;
};

const formatRelativeTime = (date) => {
    return moment(date).fromNow();
};

const getDescriptionPreview = (description) => {
    const plainText = description.replace(/<[^>]+>/g, '');
    return plainText.length > 100 ? plainText.slice(0, 100) + '...' : plainText;
};
</script>

<style scoped>
/* Estilização existente */
.description-preview {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.5em;
    max-height: 3em;
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

/* Definir a cor marrom claro para os filtros */
.text-brown-light {
    color: #8B4513;
}

/* Cor mais clara para o hover */
.text-brown-lighter {
    color: #A0522D;
}

/* Estilo para o filtro ativo */
.bg-yellow-500 {
    background-color: #f59e0b;
}

/* Ajuste para o texto branco no filtro ativo (apenas para 'Todos') */
.bg-yellow-500.text-white {
    color: #ffffff;
}

/* Estilo para a faixa no topo */
.faixa-topo {
    background-color: #1e90ff;
    color: #ffffff;
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
}

/* Estilo para o ícone do filtro */
li i {
    font-size: 1rem;
    transition: color 0.3s ease;
}

/* Ajuste para o ícone quando o filtro está ativo */
li.text-yellow-900 i {
    color: inherit;
}

/* Ajuste para o ícone de 'Todas as Categorias' quando ativo */
li.text-black i {
    color: #000000;
}

/* Estilo para o sombreamento das tags */
.tag-shadow {
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6);
}

/* Transição para a lista de posts */
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.5s ease;
}

.fade-enter-from, .fade-leave-to {
    opacity: 0;
}

.post-list {
    min-height: 100px;
}
</style>
