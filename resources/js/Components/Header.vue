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
                <a href="/meus-posts"
                    class="py-2 px-4 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 hover:-translate-y-1 transition-transform"
                    :class="{ 'bg-indigo-500 text-white': activeMenu === 'meus-posts' }"
                    @click="setActiveMenu('meus-posts')">Meus Posts</a>
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
                        <input type="text" placeholder="Pesquisar..."
                            class="w-full p-1 pl-10 rounded-2xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
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
                            class="absolute bg-white shadow-lg rounded-2xl border p-4 w-96  max-h-96 overflow-y-auto left-0 transform -translate-x-full ">
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
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { router } from '@inertiajs/vue3';

const logout = () => {
    router.post(route('logout'));
};
const notifications = ref([]);
const showPopup = ref(false);
const unreadCount = ref(0);
const isDropdownOpen = ref(false);
const isSearchExpanded = ref(false);
const activeMenu = ref('dashboard');

// Função para alternar o estado do dropdown
const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
};

// Função para alternar o estado do campo de pesquisa
const toggleSearch = (event) => {
    event.stopPropagation();
    isSearchExpanded.value = !isSearchExpanded.value;
};

const toggleNotificationPopup = () => {
    showPopup.value = !showPopup.value;
}

// Função para fechar o dropdown
const closeDropdown = () => {
    isDropdownOpen.value = false;
};

// Função para fechar a pesquisa
const closeSearch = () => {
    isSearchExpanded.value = false;
};

const closeNotifications = () => {
    showPopup.value = false;
};

// Verifica se o clique foi fora do dropdown ou do campo de pesquisa
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

// Função para marcar todas as notificações como lidas
const markAllAsRead = async () => {
    try {
        await axios.post('/notifications/mark-as-read'); // Marca todas as notificações como lidas no backend
        unreadCount.value = 0; // Zera o contador no frontend
        notifications.value = notifications.value.map(notification => ({
            ...notification,
            read_at: new Date(), // Marca como lida localmente
        }));
    } catch (error) {
        console.error('Erro ao marcar notificações como lidas:', error);
    }
};
/// Buscar notificações do backend
onMounted(() => {
    axios
        .get('/notifications')
        .then(response => {
            notifications.value = response.data.notifications;
            unreadCount.value = response.data.unread_count; // Contador de notificações não lidas
        })
        .catch(error => {
            console.error('Erro ao carregar notificações:', error);
        });
});

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
})
// Remove o evento de clique fora do componente quando o componente for desmontado
onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});

// Função para alterar o menu ativo
const setActiveMenu = (menu) => {
    activeMenu.value = menu;
};
</script>

<style scoped>
/* Adicione estilos específicos para este componente aqui */
</style>
