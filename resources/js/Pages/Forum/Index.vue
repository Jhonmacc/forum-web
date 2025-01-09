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
                    class="py-2 px-4 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-transform transform hover:-translate-y-1"
                    :class="{ 'bg-indigo-500 text-white': activeMenu === 'forum' }" @click="setActiveMenu('forum')">
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

        <!-- Filtros de Posts -->
        <!-- <nav class="flex justify-center w-full space-x-4 items-center mt-4">
            <button
                @click="filterPosts('Todos')"
                class="py-2 px-4 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-transform transform hover:-translate-y-1">
                Todos os Posts
            </button>
            <button
                @click="filterPosts('Populares')"
                class="py-2 px-4 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-transform transform hover:-translate-y-1">
                Populares
            </button>
            <button
                @click="filterPosts('Recentes')"
                class="py-2 px-4 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-transform transform hover:-translate-y-1">
                Recentes
            </button>
        </nav> -->

        <!-- Conteúdo Principal -->
        <div class="flex flex-1">
            <!-- Menu Lateral -->
            <aside class="w-1/4 bg-white shadow-md p-6">
                <!-- Botão Criar Post -->
                <button @click="openCreatePostModal"
                    class="w-full py-2 px-4 mb-4 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 shadow-md transition-transform transform hover:-translate-y-1">
                    + Criar Post
                </button>
                <h2 class="text-xl font-bold text-gray-700 mb-4">Categorias</h2>
                <button @click="filterPosts('Suporte')"
                    class="w-full py-2 px-4 mb-4 bg-blue-500 text-white font-medium rounded-lg hover:bg-blue-600 shadow-md transition-transform transform hover:-translate-y-1">
                    Suporte
                </button>
                <button @click="filterPosts('Sugestão')"
                    class="w-full py-2 px-4 mb-4 bg-green-500 text-white font-medium rounded-lg hover:bg-green-600 shadow-md transition-transform transform hover:-translate-y-1">
                    Sugestão
                </button>
            </aside>

            <!-- Timeline -->
            <main class="flex-1 p-6">
                <h1 class="text-2xl font-bold text-center text-gray-700 mb-6">
                    Bem-vindo ao Fórum 🎉
                </h1>
                <p class="text-center text-gray-500 mb-8">
                    Compartilhe suas ideias e feedback com a comunidade!
                </p>

                <div v-if="posts.data.length === 0" class="text-center text-gray-500">
                    Nenhum post encontrado.
                </div>

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
                        <span class="text-sm text-gray-400">{{ post.comments_count || 0 }} comentários</span>
                    </div>

                    <!-- Tags -->
                    <div class="mt-4">
                        <span v-for="tag in post.tags" :key="tag.id" :style="{ backgroundColor: tag.color }"
                            class="inline-block px-3 py-1 mr-2 rounded text-sm text-white font-medium"
                            style="text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6);">
                            {{ tag.name }}
                        </span>
                    </div>
                </div>
            </main>
        </div>

        <!-- Modal de Criação de Post -->
        <create-post-modal v-if="showCreatePostModal" @close="closeCreatePostModal" @success="refreshPosts" />
    </div>
</template>

<script>
import CreatePostModal from './CreatePostModal.vue';
import moment from 'moment'; // Importa o moment.js
import 'moment/locale/pt-br'; // Importa o idioma português

moment.locale('pt-br'); // Define o idioma global para português

export default {
    components: { CreatePostModal },
    props: {
        posts: Object, // Recebe posts do Laravel/Inertia
    },
    data() {
        return {
            showCreatePostModal: false,
            activeMenu: 'forum', // Marca o Fórum como o ativo por padrão
        };
    },
    methods: {
        isLightColor(color) {
            // Converte a cor hexadecimal para RGB
            const rgb = parseInt(color.slice(1), 16);
            const r = (rgb >> 16) & 0xff;
            const g = (rgb >> 8) & 0xff;
            const b = (rgb >> 0) & 0xff;

            // Calcula a luminosidade média
            const brightness = 0.2126 * r + 0.7152 * g + 0.0722 * b;

            return brightness > 128; // Se a luminosidade for maior que 128, é uma cor clara
        },
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
        changePage(page) {
            this.$inertia.get(`/forum?page=${page}`); // Atualiza os posts na página correta
        },
        formatRelativeTime(date) {
            return moment(date).fromNow(); // Retorna o tempo relativo formatado em português
        },
    },
};
</script>
