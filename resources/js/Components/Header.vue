<template>
    <div class="flex flex-col bg-white">
        <!-- Menu Superior -->
        <header class="w-full shadow-md p-2">
            <nav class="flex justify-center w-full space-x-4 items-center">
                <a href="/dashboard"
                    class="py-2 px-4 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 hover:-translate-y-1 transition-transform"
                    :class="{ 'bg-indigo-500 text-white': activeMenu === 'dashboard' }"
                    @click="setActiveMenu('dashboard')">Dashboard</a>
                <a href="/forum"
                    class="py-2 px-4 bg-gray-200 text-gray-700 rounded-lg hover:bg-yellow-500 hover:-translate-y-1 transition-transform"
                    :class="{ 'bg-yellow-400 text-white': activeMenu === 'forum' }"
                    @click="setActiveMenu('forum')">Fórum</a>
                <!-- Botão Meus Posts -->
                <button
                    class="py-2 px-4 bg-gray-200 text-gray-700 rounded-lg hover:bg-yellow-500 hover:-translate-y-1 transition-transform"
                    :class="{ 'bg-yellow-400 text-white': activeMenu === 'meus-posts' }" @click="goToMyProfile">Meus
                    Posts</button>
            </nav>
        </header>

        <!-- Barra de Toogle e Pesquisa -->
        <div class="w-full bg-zinc-700 shadow-md p-2 text-gray-700">
            <div class="flex items-center justify-end">
                <div class="flex items-center space-x-6">
                    <!-- Campo de Pesquisa -->
                    <div ref="searchField"
                        class="relative flex items-center space-x-2 p-1 rounded-lg transition-all duration-500 ease-in-out"
                        :class="{ 'w-60': !isSearchExpanded, 'w-96': isSearchExpanded }" @click="toggleSearch($event)">
                        <i class="fa-solid fa-magnifying-glass text-gray-500 absolute left-7"></i>
                        <input v-model="searchQuery" type="text" placeholder="Pesquisar..."
                            class="w-full p-1 pl-10 rounded-2xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            @input="handleSearch" @focus="isSearchExpanded = true" @blur="onSearchBlur" />
                        <!-- Spinner -->
                        <div v-if="isLoading" class="absolute right-2">
                            <i class="fas fa-spinner fa-spin text-gray-500"></i>
                        </div>
                        <!-- Lista Suspensa de Resultados -->
                        <div v-if="searchResults.length > 0 && isSearchExpanded"
                            class="absolute top-full left-0 w-full bg-white shadow-lg rounded-lg mt-1 max-h-60 overflow-y-auto z-10">
                            <a v-for="(result, index) in searchResults" :key="index" :href="result.url"
                                class="block p-2 hover:bg-gray-100 cursor-pointer">
                                {{ result.title }}
                            </a>
                        </div>
                    </div>
                    <div class="relative" ref="notificationsAlert">
                        <!-- Ícone do sininho de notificações -->
                        <div @click="toggleNotificationPopup" class="cursor-pointer relative">
                            <i :class="['fa-bell', { 'text-yellow-500': unreadCount > 0 }]" class="fa-regular"></i>
                            <span v-if="unreadCount > 0"
                                class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full px-1">
                                {{ unreadCount }}
                            </span>
                        </div>
                        <!-- Popup de notificações -->
                        <div v-if="showPopup"
                            class="absolute bg-white shadow-lg rounded-2xl border p-4 w-96 max-h-96 overflow-y-auto left-0 transform -translate-x-full">
                            <div class="flex justify-between items-center mb-2">
                                <h3 class="text-lg font-semibold">Notificações</h3>
                                <i class="fa-solid fa-check cursor-pointer text-gray-500 hover:text-green-500"
                                    @click="markAllAsRead" title="Marca todas as notificações como lidas!"></i>
                            </div>
                            <ul>
                                <li v-for="notification in notifications" :key="notification.id"
                                    class="mb-2 border-b pb-2">
                                    <a :href="`/posts/${notification.data.post_id}/edit`"
                                        class="text-blue-500 hover:underline" v-html="notification.data.message"></a>
                                    <span v-if="!notification.read_at" class="text-red-500 text-xs ml-2">(Não
                                        lida)</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- Acesso ao Perfil -->
                    <div class="relative dropdown-container">
                        <button @click="toggleDropdown"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-full text-white hover:bg-neutral-500 focus:outline-none focus:bg-neutral-500 transition ease-in-out duration-150">
                            <img class="size-8 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url"
                                :alt="$page.props.auth.user.name">
                            <span class="ml-2 text-white">{{ $page.props.auth.user.name }}</span>
                            <i class="fas fa-chevron-down ml-2 text-sm"></i>
                        </button>

                        <div v-show="isDropdownOpen"
                            class="absolute right-0 mt-2 w-48 bg-white text-white rounded-md shadow-lg z-10"
                            @click.stop>
                            <div class="py-1">
                                <a :href="route('profile.show')"
                                    class="block px-4 py-2 text-sm text-black hover:bg-yellow-100">
                                    <i class="fas fa-user mr-2"></i> Perfil
                                </a>
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
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';

const page = usePage();

const logout = () => {
    router.post(route('logout'));
};

const notifications = ref([]);
const showPopup = ref(false);
const unreadCount = ref(0);
const isDropdownOpen = ref(false);
const isSearchExpanded = ref(false);
const activeMenu = ref('forum');
const searchQuery = ref('');
const searchResults = ref([]);
const isLoading = ref(false);

const updateActiveMenu = () => {
    const currentPath = window.location.pathname;
    if (currentPath === '/dashboard') {
        activeMenu.value = 'dashboard';
    } else if (currentPath === '/forum') {
        activeMenu.value = 'forum';
    } else if (currentPath.startsWith('/users/')) {
        activeMenu.value = 'meus-posts';
    } else {
        activeMenu.value = '';
    }
};

const goToMyProfile = () => {
    const userId = page.props.auth.user.id;
    setActiveMenu('meus-posts');
    router.visit(`/users/${userId}`);
};

const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
};

const toggleSearch = (event) => {
    event.stopPropagation();
    isSearchExpanded.value = true;
};

const onSearchBlur = () => {
    // Adiciona um pequeno delay para permitir o clique nos resultados
    setTimeout(() => {
        isSearchExpanded.value = false;
    }, 200);
};

const toggleNotificationPopup = () => {
    showPopup.value = !showPopup.value;
};

const closeDropdown = () => {
    isDropdownOpen.value = false;
};

const closeSearch = () => {
    isSearchExpanded.value = false;
    searchResults.value = [];
};

const closeNotifications = () => {
    showPopup.value = false;
};

const handleClickOutside = (event) => {
    if (!event.target.closest('.dropdown-container')) {
        closeDropdown();
    }
    if (!event.target.closest('.searchField') && !event.target.closest('.relative')) {
        closeSearch();
    }
    if (!event.target.closest('.notificationsAlert') && !event.target.closest('.relative')) {
        closeNotifications();
    }
};

const markAllAsRead = async () => {
    try {
        await axios.post('/notifications/mark-as-read');
        unreadCount.value = 0;
        notifications.value = notifications.value.map(notification => ({
            ...notification,
            read_at: new Date(),
        }));
    } catch (error) {
        console.error('Erro ao marcar notificações como lidas:', error);
    }
};

const handleSearch = async () => {
    if (!searchQuery.value.trim()) {
        searchResults.value = [];
        return;
    }

    isLoading.value = true;
    try {
        const response = await axios.get(`/search-posts?query=${encodeURIComponent(searchQuery.value)}`);
        searchResults.value = response.data;
    } catch (error) {
        console.error('Erro ao buscar posts:', error);
        searchResults.value = [];
    } finally {
        isLoading.value = false;
    }
};

const redirectToPost = (postId) => {
    if (!postId) {
        console.error('ID do post inválido');
        return;
    }

    // Usando window.location para garantir o redirecionamento
    window.location.href = `/posts/${postId}`;

    // Alternativa com Inertia.js (comente a linha acima e descomente esta se preferir)
    // router.visit(`/posts/${postId}`);
};

onMounted(() => {
    axios
        .get('/notifications')
        .then(response => {
            notifications.value = response.data.notifications;
            unreadCount.value = response.data.unread_count;
        })
        .catch(error => {
            console.error('Erro ao carregar notificações:', error);
        });

    updateActiveMenu();
    router.on('navigate', () => {
        updateActiveMenu();
    });

    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});

const setActiveMenu = (menu) => {
    activeMenu.value = menu;
};

watch(isSearchExpanded, (newValue) => {
    if (!newValue && !searchQuery.value) {
        searchResults.value = [];
    }
});
</script>

<style scoped>
/* Estilize o spinner e a lista suspensa conforme necessário */
.fa-spinner {
    font-size: 1.2rem;
}
</style>
