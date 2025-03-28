<!-- resources/js/Pages/Forum/EditPost.vue -->
<script setup>
import Header from '@/Components/Header.vue';
</script>

<template>
    <div class="bg-white min-h-screen">
        <!-- Componente do Cabeçalho e Menu Header -->
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
                hover:underline hover:-translate-y-1 transition-transform transform translate-y-4 -translate-x-2"
                :src="$page.props.post.user.profile_photo_url" :alt="$page.props.post.user.name"
                @click="$inertia.get(`/users/${post?.user?.id}`)">
            <div class="flex items-center space-x-2">
                <button class="hover:underline hover:-translate-y-1 transition-transform transform"
                    @click="$inertia.get(`/users/${post?.user?.id}`)">
                    {{ post?.user?.name }}
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

            <div class="flex justify-between mt-8 mb-4 p-6 relative dropdown-container">
                <!-- Curtida e Contagem (Totalmente à esquerda) -->
                <button @click="toggleLikePost" class="focus:outline-none flex items-center space-x-1">
                    <i :class="{ 'fa-regular fa-heart fa-lg': !post?.liked, 'fa-solid fa-heart text-red-500': post?.liked }"
                        class="transition duration-200 ease-in-out text-lg"></i>
                    <span class="text-sm text-gray-600">{{ post?.likes_count || 0 }}</span>
                </button>

                <!-- Botão principal com os três pontos (Totalmente à direita) -->
                <!-- Mostrar apenas se o usuário autenticado for o autor do post -->
                <div v-if="localIsAuthenticated && $page.props.auth.user.id === post?.user_id" class="relative">
                    <button @click="toggleDropdown" class="p-2 bg-gray-200 text-gray-700 rounded-full hover:bg-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 3a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 7a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 7a1.5 1.5 0 110-3 1.5 1.5 0 010 3z" />
                        </svg>
                    </button>

                    <!-- Dropdown menu -->
                    <div v-if="showDropdown"
                        class="absolute top-full right-0 mt-2 w-32 bg-white border border-gray-300 rounded-lg shadow-lg z-10">
                        <button @click="editPost" class="flex items-center w-full px-4 py-2 text-gray-700 hover:bg-gray-100">
                            <i class="fa-regular fa-pen-to-square mr-1"></i>
                            Editar
                        </button>
                        <button @click="deletePost" class="flex items-center w-full px-4 py-2 text-gray-700 hover:bg-gray-100">
                            <i class="fa-regular fa-trash-can mr-1"></i>
                            Deletar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comentários -->
        <div class="relative p-6 bg-gray-100 rounded-lg max-w-4xl mx-auto mt-8">
            <h3 class="text-lg font-semibold mb-4">Comentários</h3>

            <div v-if="localIsAuthenticated" class="mb-4">
                <textarea
                    v-model="newComment"
                    @input="detectMention"
                    placeholder="Deixe seu comentário..."
                    class="w-full p-2 border rounded"
                ></textarea>
                <ul v-if="showSuggestions" class="suggestions-list">
                    <li
                        v-for="user in suggestions"
                        :key="user.id"
                        @click="addMention(user)"
                    >
                        {{ user.username }}
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
                <div v-for="(comment, index) in post.comments" :key="comment.id" class="p-4 bg-white shadow rounded-lg comment-container">
                    <div class="flex items-start space-x-3">
                        <!-- Avatar do autor -->
                        <img
                            :src="comment.user.profile_photo_url || 'https://via.placeholder.com/40'"
                            alt="Avatar"
                            class="w-10 h-10 rounded-full object-cover cursor-pointer"
                            @click="$inertia.get(`/users/${comment.user.id}`)"
                        >
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-1">
                                <div>
                                    <span
                                        class="font-semibold text-gray-800 cursor-pointer hover:underline"
                                        @click="$inertia.get(`/users/${comment.user.id}`)"
                                    >
                                        {{ comment.user.name }}
                                    </span>
                                    <span class="text-sm text-gray-500 ml-2">{{ formatRelativeTime(comment?.created_at) }}</span>
                                    <p v-if="comment.liked" class="text-xs text-gray-500 ml-2 mb-0">Você curtiu esse comentário.</p>
                                </div>
                                <!-- Dropdown para edição/exclusão (apenas para o autor) -->
                                <div v-if="localIsAuthenticated && $page.props.auth.user.id === comment.user_id" class="relative dropdown-container">
                                    <button @click="toggleCommentDropdown(comment.id)" class="p-1 bg-gray-200 text-gray-700 rounded-full hover:bg-gray-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10 3a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 7a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 7a1.5 1.5 0 110-3 1.5 1.5 0 010 3z" />
                                        </svg>
                                    </button>
                                    <div v-if="activeCommentDropdown === comment.id"
                                        class="absolute right-0 mt-2 w-32 bg-white border border-gray-300 rounded-lg shadow-lg z-10">
                                        <button @click="openEditCommentModal(comment)" class="flex items-center w-full px-4 py-2 text-gray-700 hover:bg-gray-100">
                                            <i class="fa-regular fa-pen-to-square mr-1"></i>
                                            Editar
                                        </button>
                                        <button @click="deleteComment(comment.id)" class="flex items-center w-full px-4 py-2 text-gray-700 hover:bg-gray-100">
                                            <i class="fa-regular fa-trash-can mr-1"></i>
                                            Excluir
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <p v-html="comment.renderedContent" class="text-gray-700 mb-2"></p>
                            <!-- Curtidas e botão de resposta -->
                            <div class="flex items-center space-x-3">
                                <button @click="toggleLike(comment)" class="flex items-center space-x-1 text-gray-600 hover:text-red-500 transition">
                                    <i :class="{ 'fa-regular fa-heart': !comment.liked, 'fa-solid fa-heart text-red-500': comment.liked }" class="text-lg"></i>
                                    <span class="text-sm">{{ comment.likes_count }}</span>
                                </button>
                                <button @click="openReplyModal(comment)" class="flex items-center space-x-1 text-blue-500 hover:text-blue-700 transition">
                                    <i class="fa-regular fa-comment text-sm"></i>
                                    <span class="text-sm">Responder</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Modal de resposta para o comentário -->
                    <div v-if="activeReplyCommentId === comment.id" class="mt-4">
                        <textarea
                            v-model="replyBody"
                            placeholder="Escreva sua resposta..."
                            @input="detectReplyMention($event, 'replyToComment')"
                            class="w-full p-2 border rounded-md focus:ring focus:ring-indigo-300 resize-none"
                            rows="3"
                        ></textarea>
                        <ul v-if="showReplySuggestions" class="suggestions-list">
                            <li
                                v-for="user in replySuggestions"
                                :key="user.id"
                                @click="addReplyMention(user, 'replyToComment')"
                                class="p-2 cursor-pointer hover:bg-gray-100"
                            >
                                {{ user.username }}
                            </li>
                        </ul>
                        <div class="flex justify-end mt-2">
                            <button @click="replyToComment(comment.id)" class="px-4 py-2 bg-blue-500 text-white rounded">
                                Enviar Resposta
                            </button>
                            <button @click="closeReplyModal" class="px-4 py-2 bg-gray-300 text-black rounded ml-2">
                                Cancelar
                            </button>
                        </div>
                    </div>

                    <!-- Respostas do comentário -->
                    <div v-if="comment.replies && comment.replies.length" class="mt-4 space-y-3">
                        <div v-for="(reply, rIndex) in getVisibleReplies(comment)" :key="reply?.id" class="reply-container pl-6 border-l-2 border-blue-200">
                            <div class="flex items-start space-x-3">
                                <!-- Avatar do autor da resposta -->
                                <img
                                    :src="reply.user?.profile_photo_url || 'https://via.placeholder.com/36'"
                                    alt="Avatar"
                                    class="w-9 h-9 rounded-full object-cover cursor-pointer"
                                    @click="$inertia.get(`/users/${reply.user?.id}`)"
                                >
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-1">
                                        <div>
                                            <span
                                                v-if="reply?.user?.name"
                                                class="font-semibold text-gray-800 cursor-pointer hover:underline"
                                                @click="$inertia.get(`/users/${reply.user?.id}`)"
                                            >
                                                {{ reply.user.name }}
                                            </span>
                                            <span class="text-sm text-gray-500 ml-2">{{ formatRelativeTime(reply?.created_at) }}</span>
                                            <p v-if="reply.liked" class="text-xs text-gray-500 ml-2 mb-0">Você curtiu esse comentário.</p>
                                        </div>
                                        <!-- Dropdown para edição/exclusão (apenas para o autor) -->
                                        <div v-if="localIsAuthenticated && $page.props.auth.user.id === reply.user_id" class="relative dropdown-container">
                                            <button @click="toggleReplyDropdown(reply.id, `reply-${index}-${rIndex}`)" class="p-1 bg-gray-200 text-gray-700 rounded-full hover:bg-gray-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M10 3a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 7a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 7a1.5 1.5 0 110-3 1.5 1.5 0 010 3z" />
                                                </svg>
                                            </button>
                                            <div v-if="activeReplyDropdown && activeReplyDropdown.replyId === reply.id && activeReplyDropdown.uniqueKey === `reply-${index}-${rIndex}`"
                                                class="absolute right-0 mt-2 w-32 bg-white border border-gray-300 rounded-lg shadow-lg z-10">
                                                <button @click="openEditReplyModal(reply)" class="flex items-center w-full px-4 py-2 text-gray-700 hover:bg-gray-100">
                                                    <i class="fa-regular fa-pen-to-square mr-1"></i>
                                                    Editar
                                                </button>
                                                <button @click="deleteReply(reply.id)" class="flex items-center w-full px-4 py-2 text-gray-700 hover:bg-gray-100">
                                                    <i class="fa-regular fa-trash-can mr-1"></i>
                                                    Excluir
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <p v-if="reply?.renderedBody" class="text-gray-700 mb-2" v-html="reply.renderedBody"></p>
                                    <!-- Curtidas e botão de resposta -->
                                    <div class="flex items-center space-x-3">
                                        <button @click="toggleLikeReply(reply)" class="flex items-center space-x-1 text-gray-600 hover:text-red-500 transition">
                                            <i :class="{ 'fa-regular fa-heart': !reply.liked, 'fa-solid fa-heart text-red-500': reply.liked }" class="text-lg"></i>
                                            <span class="text-sm">{{ reply.likes_count }}</span>
                                        </button>
                                        <button @click="openReplyToReplyModal(reply)" class="flex items-center space-x-1 text-blue-500 hover:text-blue-700 transition">
                                            <i class="fa-regular fa-comment text-sm"></i>
                                            <span class="text-sm">Responder</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Respostas aninhadas (children) -->
                            <div v-if="reply.children && reply.children.length" class="mt-3 space-y-3">
    <div v-for="(childReply, childIndex) in getVisibleChildren(reply)" :key="childReply.id" class="child-reply-container pl-6 border-l-2 border-blue-300">
        <div class="flex items-start space-x-3">
            <!-- Avatar do autor da resposta aninhada -->
            <img
                :src="childReply.user?.profile_photo_url || 'https://via.placeholder.com/32'"
                alt="Avatar"
                class="w-8 h-8 rounded-full object-cover cursor-pointer"
                @click="$inertia.get(`/users/${childReply.user?.id}`)"
            >
            <div class="flex-1">
                <div class="flex items-center justify-between mb-1">
                    <div>
                        <span
                            v-if="childReply?.user?.name"
                            class="font-semibold text-gray-800 cursor-pointer hover:underline"
                            @click="$inertia.get(`/users/${childReply.user?.id}`)"
                        >
                            {{ childReply.user.name }}
                        </span>
                        <span class="text-sm text-gray-500 ml-2">{{ formatRelativeTime(childReply?.created_at) }}</span>
                        <p v-if="childReply.liked" class="text-xs text-gray-500 ml-2 mb-0">Você curtiu esse comentário.</p>
                    </div>
                    <!-- Dropdown para edição/exclusão (apenas para o autor) -->
                    <div v-if="localIsAuthenticated && $page.props.auth.user.id === childReply.user_id" class="relative dropdown-container">
                        <button @click="toggleReplyDropdown(childReply.id, `child-${index}-${rIndex}-${childIndex}`)" class="p-1 bg-gray-200 text-gray-700 rounded-full hover:bg-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 3a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 7a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 7a1.5 1.5 0 110-3 1.5 1.5 0 010 3z" />
                            </svg>
                        </button>
                        <div v-if="activeReplyDropdown && activeReplyDropdown.replyId === childReply.id && activeReplyDropdown.uniqueKey === `child-${index}-${rIndex}-${childIndex}`"
                            class="absolute right-0 mt-2 w-32 bg-white border border-gray-300 rounded-lg shadow-lg z-10">
                            <button @click="openEditReplyModal(childReply)" class="flex items-center w-full px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i class="fa-regular fa-pen-to-square mr-1"></i>
                                Editar
                            </button>
                            <button @click="deleteReply(childReply.id)" class="flex items-center w-full px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i class="fa-regular fa-trash-can mr-1"></i>
                                Excluir
                            </button>
                        </div>
                    </div>
                </div>
                <p v-if="childReply?.renderedBody" class="text-gray-700 mb-2" v-html="childReply.renderedBody"></p>
                <div class="flex items-center space-x-3">
                    <button @click="toggleLikeReply(childReply)" class="flex items-center space-x-1 text-gray-600 hover:text-red-500 transition">
                        <i :class="{ 'fa-regular fa-heart': !childReply.liked, 'fa-solid fa-heart text-red-500': childReply.liked }" class="text-lg"></i>
                        <span class="text-sm">{{ childReply.likes_count }}</span>
                    </button>
                    <button @click="openReplyToReplyModal(childReply)" class="flex items-center space-x-1 text-blue-500 hover:text-blue-700 transition">
                        <i class="fa-regular fa-comment text-sm"></i>
                        <span class="text-sm">Responder</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Botão para ver mais/ocultar respostas aninhadas -->
    <div v-if="reply.children.length > 2" class="mt-2 pl-6">
        <button v-if="!expandedReplies.includes(reply.id)" @click="expandChildren(reply)" class="text-blue-500 text-sm hover:underline">
            Ver mais respostas
        </button>
        <button v-else @click="collapseChildren(reply)" class="text-blue-500 text-sm hover:underline">
            Ocultar respostas
        </button>
    </div>
</div>

                            <!-- Modal de resposta para uma resposta -->
                            <div v-if="activeReplyId === reply.id" class="mt-4">
                                <textarea
                                    v-model="replyToReplyBody"
                                    placeholder="Escreva sua resposta..."
                                    @input="detectReplyMention($event, 'replyToReply')"
                                    class="w-full p-2 border rounded-md focus:ring focus:ring-indigo-300 resize-none"
                                    rows="3"
                                ></textarea>
                                <ul v-if="showReplySuggestions" class="suggestions-list">
                                    <li
                                        v-for="user in replySuggestions"
                                        :key="user.id"
                                        @click="addReplyMention(user, 'replyToReply')"
                                        class="p-2 cursor-pointer hover:bg-gray-100"
                                    >
                                        {{ user.username }}
                                    </li>
                                </ul>
                                <div class="flex justify-end mt-2">
                                    <button @click="replyToReply(reply)" class="px-4 py-2 bg-blue-500 text-white rounded">
                                        Enviar Resposta
                                    </button>
                                    <button @click="closeReplyToReplyModal" class="px-4 py-2 bg-gray-300 text-black rounded ml-2">
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Botão para ver mais/ocultar respostas -->
                        <div v-if="comment.replies.length > 3" class="mt-2">
                            <button v-if="!expandedComments.includes(comment.id)" @click="expandReplies(comment)" class="text-blue-500 text-sm hover:underline">
                                Ver mais respostas
                            </button>
                            <button v-else @click="collapseReplies(comment)" class="text-blue-500 text-sm hover:underline">
                                Ocultar respostas
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="text-gray-500">Nenhum comentário ainda. Seja o primeiro a comentar!</div>
        </div>

        <!-- Modal de edição de comentário -->
        <div v-if="editingComment" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
            <div class="bg-white rounded shadow p-6 w-full max-w-4xl text-black">
                <h2 class="text-xl font-semibold mb-4">Editar Comentário</h2>
                <textarea
                    v-model="editCommentContent"
                    @input="detectMention"
                    placeholder="Edite seu comentário..."
                    class="w-full p-2 border rounded resize-none"
                    rows="4"
                ></textarea>
                <ul v-if="showSuggestions" class="suggestions-list">
                    <li
                        v-for="user in suggestions"
                        :key="user.id"
                        @click="addMention(user)"
                        class="p-2 cursor-pointer hover:bg-gray-100"
                    >
                        {{ user.username }}
                    </li>
                </ul>
                <div class="flex justify-end mt-4 space-x-4">
                    <button @click="closeEditCommentModal" class="py-2 px-4 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                        Cancelar
                    </button>
                    <button @click="updateComment" class="py-2 px-4 bg-indigo-500 text-white rounded hover:bg-indigo-600">
                        Atualizar Comentário
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal de edição de resposta -->
        <div v-if="editingReply" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
            <div class="bg-white rounded shadow p-6 w-full max-w-4xl text-black">
                <h2 class="text-xl font-semibold mb-4">Editar Resposta</h2>
                <textarea
                    v-model="editReplyBody"
                    @input="detectReplyMention($event, 'replyToReply')"
                    placeholder="Edite sua resposta..."
                    class="w-full p-2 border rounded resize-none"
                    rows="4"
                    :disabled="isUpdatingReply"
                ></textarea>
                <ul v-if="showReplySuggestions" class="suggestions-list">
                    <li
                        v-for="user in replySuggestions"
                        :key="user.id"
                        @click="addReplyMention(user, 'replyToReply')"
                        class="p-2 cursor-pointer hover:bg-gray-100"
                    >
                        {{ user.username }}
                    </li>
                </ul>
                <div class="flex justify-end mt-4 space-x-4">
                    <button
                        @click="closeEditReplyModal"
                        class="py-2 px-4 bg-gray-300 text-gray-700 rounded hover:bg-gray-400"
                        :disabled="isUpdatingReply"
                    >
                        Cancelar
                    </button>
                    <button
                        @click="updateReply"
                        class="py-2 px-4 bg-indigo-500 text-white rounded hover:bg-indigo-600 flex items-center"
                        :disabled="isUpdatingReply"
                    >
                        <span v-if="isUpdatingReply" class="animate-spin mr-2">⏳</span>
                        {{ isUpdatingReply ? 'Atualizando...' : 'Atualizar Resposta' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal de Edição do Post -->
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
                    <button @click="openTaggingModal" class="py-2 px-4 bg-gray-500 text-white rounded hover:bg-gray-600">
                        Escolha as Tags
                    </button>
                </div>

                <!-- Exibição das Tags Selecionadas -->
                <div v-if="postToEdit.tags.length > 0" class="mb-4">
                    <h3 class="font-medium text-gray-700">Tags Selecionadas:</h3>
                    <div class="container w-full h-1/24 grid grid-cols-4">
                        <span v-for="(tag, index) in postToEdit.tags" :key="index" class="flex items-center space-x-2 w-1/2">
                            <span class="w-4 h-4 rounded-full" :style="{ backgroundColor: tag.color }"></span>
                            <span>{{ tag?.name }}</span>
                        </span>
                    </div>
                </div>

                <p v-if="errors.tags" class="text-red-500 text-sm mt-1">{{ errors.tags }}</p>

                <!-- Botões de Ação -->
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
        <tagging-modal v-if="showTaggingModal" @close="closeTaggingModal" @select-tags="setTags" :selected-tags="postToEdit.tags" />
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
            localIsAuthenticated: this.$page.props.auth.user !== null,
            post: null,
            postToEdit: { title: '', description: '', tags: [] },
            quillEditor: null,
            showEditModal: false,
            showTaggingModal: false,
            errors: {},
            formattedDate: '',
            showTooltip: false,
            showDropdown: false,
            newComment: '',
            content: '',
            suggestions: [],
            showSuggestions: false,
            mentions: [],
            comments: [],
            showReplyModal: false,
            selectedComment: null,
            replyBody: '',
            activeReplyCommentId: null,
            expandedComments: [],
            expandedReplies: [],
            activeReplyId: null,
            replyToReplyBody: '',
            replySuggestions: [],
            showReplySuggestions: false,
            isAddingMention: false,
            lastMentionQuery: '',
            lastReplyMentionQuery: '',
            renderedComments: [],
            renderedReplies: new Map(),
            // Novas propriedades
            activeCommentDropdown: null, // Controla qual dropdown de comentário está aberto
            activeReplyDropdown: null, // Controla qual dropdown de resposta está aberto (agora um objeto { replyId, uniqueKey })
            editingComment: null, // Guarda o comentário sendo editado
            editingReply: null, // Guarda a resposta sendo editada
            editCommentContent: '', // Armazena o conteúdo do comentário sendo editado
            editReplyBody: '', // Armazena o corpo da resposta sendo editado
            isUpdatingReply: false, // Controla o estado de carregamento ao atualizar a resposta
        };
    },
    computed: {
        canEditOrDeleteComment() {
            return (comment) => this.$page.props.auth.user.id === comment.user_id;
        }
    },
    methods: {
        getVisibleReplies(comment) {
            if (!this.expandedComments.includes(comment.id)) {
                return (comment.replies || []).slice(0, 3);
            }
            return comment.replies || [];
        },
        expandReplies(comment) {
            this.expandedComments.push(comment.id);
        },
        collapseReplies(comment) {
            this.expandedComments = this.expandedComments.filter(id => id !== comment.id);
        },
        getVisibleChildren(reply) {
            if (this.expandedReplies.includes(reply.id)) {
                return reply.children;
            }
            return reply.children.slice(0, 2); // Limita a 2 respostas aninhadas inicialmente
        },
        expandChildren(reply) {
        this.expandedReplies.push(reply.id);
        },
        collapseChildren(reply) {
            this.expandedReplies = this.expandedReplies.filter(id => id !== reply.id);
        },
        formatRelativeTime(date) {
            return moment(date).fromNow();
        },
        // Métodos para gerenciar dropdowns
        toggleCommentDropdown(commentId) {
            this.activeCommentDropdown = this.activeCommentDropdown === commentId ? null : commentId;
            this.activeReplyDropdown = null; // Fecha qualquer dropdown de resposta aberto
        },
        toggleReplyDropdown(replyId, uniqueKey) {
            if (this.activeReplyDropdown && this.activeReplyDropdown.replyId === replyId && this.activeReplyDropdown.uniqueKey === uniqueKey) {
                this.activeReplyDropdown = null;
            } else {
                this.activeReplyDropdown = { replyId, uniqueKey };
            }
            this.activeCommentDropdown = null; // Fecha qualquer dropdown de comentário aberto
        },
        closeDropdowns(event) {
            if (!event.target.closest('.dropdown-container')) {
                this.activeCommentDropdown = null;
                this.activeReplyDropdown = null;
                this.showDropdown = false; // Fecha também o dropdown do post
            }
        },

        // Métodos para edição de comentários
        openEditCommentModal(comment) {
            this.editingComment = comment;
            this.editCommentContent = comment.content;
            this.mentions = []; // Limpa as menções ao abrir o modal
            this.activeCommentDropdown = null;
        },
        closeEditCommentModal() {
            this.editingComment = null;
            this.editCommentContent = '';
            this.mentions = [];
            this.showSuggestions = false;
            this.suggestions = [];
        },
        async updateComment() {
            if (!this.editCommentContent.trim()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Comentário vazio!',
                    text: 'Por favor, adicione um comentário antes de enviar.',
                });
                return;
            }

            try {
                const response = await axios.put(`/comments/${this.editingComment.id}`, {
                    content: this.editCommentContent,
                    mentions: this.mentions,
                });

                if (response.data.message === 'Comentário atualizado com sucesso') {
                    const commentIndex = this.post.comments.findIndex(c => c.id === this.editingComment.id);
                    if (commentIndex !== -1) {
                        this.post.comments[commentIndex].content = this.editCommentContent;
                        this.post.comments[commentIndex].renderedContent = await this.renderComment(this.editCommentContent);
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Comentário atualizado com sucesso!',
                        showConfirmButton: false,
                        timer: 1500,
                    });
                    this.closeEditCommentModal();
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro ao atualizar comentário!',
                    text: error.response?.data.message || 'Ops, ocorreu um erro inesperado.',
                });
            }
        },
        // Métodos para edição de respostas
        openEditReplyModal(reply) {
            this.editingReply = reply;
            this.editReplyBody = reply.body;
            this.mentions = []; // Limpa as menções ao abrir o modal
            this.activeReplyDropdown = null;
        },
        closeEditReplyModal() {
            this.editingReply = null;
            this.editReplyBody = '';
            this.mentions = [];
            this.showReplySuggestions = false;
            this.replySuggestions = [];
            this.isUpdatingReply = false; // Reseta o estado de carregamento
        },
        async updateReply() {
            if (!this.editReplyBody.trim()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Resposta vazia!',
                    text: 'Por favor, adicione uma resposta antes de enviar.',
                });
                return;
            }

            this.isUpdatingReply = true;
            try {
                const response = await axios.put(`/replies/${this.editingReply.id}`, {
                    body: this.editReplyBody,
                    mentions: this.mentions,
                });

                if (response.data.message === 'Resposta atualizada com sucesso') {
                    // Encontrar e atualizar a resposta no estado local
                    let updated = false;
                    for (let comment of this.post.comments) {
                        // Procurar no nível de replies
                        const replyIndex = comment.replies.findIndex(r => r.id === this.editingReply.id);
                        if (replyIndex !== -1) {
                            comment.replies[replyIndex] = {
                                ...comment.replies[replyIndex],
                                body: this.editReplyBody,
                                renderedBody: await this.renderReply(this.editReplyBody),
                            };
                            this.renderedReplies.set(this.editingReply.id, comment.replies[replyIndex].renderedBody);
                            updated = true;
                            break;
                        }

                        // Procurar em respostas aninhadas (children)
                        for (let reply of comment.replies) {
                            const childIndex = (reply.children || []).findIndex(child => child.id === this.editingReply.id);
                            if (childIndex !== -1) {
                                reply.children[childIndex] = {
                                    ...reply.children[childIndex],
                                    body: this.editReplyBody,
                                    renderedBody: await this.renderReply(this.editReplyBody),
                                };
                                this.renderedReplies.set(this.editingReply.id, reply.children[childIndex].renderedBody);
                                updated = true;
                                break;
                            }
                        }
                        if (updated) break;
                    }

                    if (!updated) {
                        console.error('Resposta não encontrada para atualização:', this.editingReply.id);
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Resposta atualizada com sucesso!',
                        showConfirmButton: false,
                        timer: 1500,
                    });
                    this.closeEditReplyModal();
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro ao atualizar resposta!',
                    text: error.response?.data.message || 'Ops, ocorreu um erro inesperado.',
                });
            } finally {
                this.isUpdatingReply = false;
            }
        },
        async toggleLike(comment) {
            try {
                const response = await axios.post(`/comments/${comment.id}/like`);
                comment.liked = response.data.liked;
                comment.likes_count = response.data.likes_count;
                if (comment.liked) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Você curtiu o comentário!',
                        showConfirmButton: false,
                        timer: 1500,
                    });
                }
            } catch (error) {
                console.error('Erro ao curtir o comentário:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Ocorreu um erro ao tentar curtir o comentário.',
                });
            }
        },
        async toggleLikeReply(reply) {
            try {
                const response = await axios.post(`/replies/${reply.id}/like`);
                reply.liked = response.data.liked;
                reply.likes_count = response.data.likes_count;
                if (reply.liked) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Você curtiu a resposta!',
                        showConfirmButton: false,
                        timer: 1500,
                    });
                }
            } catch (error) {
                console.error('Erro ao curtir a resposta:', error.response ? error.response.data : error.message);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: error.response?.data?.message || 'Ocorreu um erro ao tentar curtir a resposta.',
                });
            }
        },
        openReplyModal(comment) {
            this.activeReplyCommentId = comment.id;
            this.replyBody = comment ? `@${comment.user.username} ` : '';
            this.lastReplyMentionQuery = '';
            this.mentions = [];
        },
        closeReplyModal() {
            this.activeReplyCommentId = null;
            this.replyBody = '';
            this.lastReplyMentionQuery = '';
            this.mentions = [];
            this.showReplySuggestions = false;
            this.replySuggestions = [];
        },
        openReplyToReplyModal(reply) {
            this.activeReplyId = reply.id;
            this.replyToReplyBody = `@${reply.user.username} `;
            this.lastReplyMentionQuery = '';
            this.mentions = [];
        },
        closeReplyToReplyModal() {
            this.activeReplyId = null;
            this.replyToReplyBody = '';
            this.lastReplyMentionQuery = '';
            this.mentions = [];
            this.showReplySuggestions = false;
            this.replySuggestions = [];
        },
        async replyToComment(commentId) {
            try {
                const response = await axios.post(`/comments/${commentId}/reply`, {
                    body: this.replyBody,
                    mentions: this.mentions,
                });

                const commentIndex = this.post.comments.findIndex(c => c.id === commentId);
                if (commentIndex !== -1) {
                    const comment = { ...this.post.comments[commentIndex] };
                    const newReply = {
                        ...response.data,
                        liked: false,
                        likes_count: 0,
                        user: response.data.user || null,
                        children: [],
                        renderedBody: await this.renderReply(response.data.body),
                    };
                    this.renderedReplies.set(newReply.id, newReply.renderedBody);
                    if (Array.isArray(comment.replies)) {
                        comment.replies.push(newReply);
                    } else {
                        comment.replies = [newReply];
                    }
                    this.post.comments[commentIndex] = comment;
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
    if (!reply || !reply.id) {
        console.error('Erro: reply ou reply.id é undefined', reply);
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Não foi possível enviar sua resposta. ID da resposta não encontrado.',
        });
        return;
    }

    try {
        const response = await axios.post(`/replies/${reply.id}/reply`, {
            body: this.replyToReplyBody,
            mentions: this.mentions,
        });

        // Encontrar o comentário pai e a resposta pai
        const commentIndex = this.post.comments.findIndex(c => c.replies.some(r => r.id === reply.id));
        if (commentIndex !== -1) {
            const comment = { ...this.post.comments[commentIndex] };
            const replyIndex = comment.replies.findIndex(r => r.id === reply.id);
            if (replyIndex !== -1) {
                const updatedReply = { ...comment.replies[replyIndex] };
                const newReply = {
                    ...response.data,
                    liked: false,
                    likes_count: 0,
                    user: response.data.user || null,
                    children: [],
                    renderedBody: await this.renderReply(response.data.body),
                };
                this.renderedReplies.set(newReply.id, newReply.renderedBody);

                // Adicionar a nova resposta ao array children da resposta pai
                if (!Array.isArray(updatedReply.children)) {
                    updatedReply.children = [];
                }
                updatedReply.children.push(newReply);

                // Atualizar a resposta pai no array de replies do comentário
                comment.replies[replyIndex] = updatedReply;

                // Atualizar o comentário no array de comentários do post
                this.post.comments[commentIndex] = comment;
            }
        }

        Swal.fire({
            icon: 'success',
            title: 'Resposta enviada com sucesso!',
            showConfirmButton: false,
            timer: 1500,
        });
        this.closeReplyToReplyModal();
    } catch (error) {
        console.error('Erro ao enviar resposta:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Não foi possível enviar sua resposta.',
        });
    }
},
        addMention(user) {
            let input;
            let targetField;

            // Determina o contexto (novo comentário ou edição de comentário)
            if (this.editingComment) {
                input = this.editCommentContent;
                targetField = 'editCommentContent';
            } else {
                input = this.newComment;
                targetField = 'newComment';
            }

            const mentionIndex = input.lastIndexOf('@');
            const afterAt = input.slice(mentionIndex + 1);
            const queryEnd = afterAt.indexOf(' ') !== -1 ? afterAt.indexOf(' ') : afterAt.length;
            const queryLength = queryEnd + 1;

            this.isAddingMention = true;
            const newText = input.slice(0, mentionIndex) + `@${user.username} ` + input.slice(mentionIndex + queryLength);
            this[targetField] = newText; // Atualiza o campo correto
            this.mentions.push(user);
            this.showSuggestions = false;
            this.suggestions = [];
            this.lastMentionQuery = user.username;
            this.$nextTick(() => {
                this.isAddingMention = false;
            });
            console.log(`Texto após adicionar menção (${targetField}):`, this[targetField]);
            console.log('Menções atuais:', this.mentions);
        },
        addReplyMention(user, type) {
            let input;
            let targetField;

            // Determina o contexto (resposta a comentário, resposta a resposta, ou edição de resposta)
            if (this.editingReply) {
                input = this.editReplyBody;
                targetField = 'editReplyBody';
            } else if (type === 'replyToComment') {
                input = this.replyBody;
                targetField = 'replyBody';
            } else if (type === 'replyToReply') {
                input = this.replyToReplyBody;
                targetField = 'replyToReplyBody';
            }

            const mentionIndex = input.lastIndexOf('@');
            const afterAt = input.slice(mentionIndex + 1);
            const queryEnd = afterAt.indexOf(' ') !== -1 ? afterAt.indexOf(' ') : afterAt.length;
            const queryLength = queryEnd + 1;

            this.isAddingMention = true;
            const newText = input.slice(0, mentionIndex) + `@${user.username} ` + input.slice(mentionIndex + queryLength);
            this[targetField] = newText; // Atualiza o campo correto
            this.mentions.push(user);
            this.showReplySuggestions = false;
            this.replySuggestions = [];
            this.lastReplyMentionQuery = user.username;
            this.$nextTick(() => {
                this.isAddingMention = false;
            });
            console.log(`Texto após adicionar menção (${targetField}):`, this[targetField]);
            console.log('Menções atuais:', this.mentions);
        },
        async renderComment(newComment) {
            console.log('Renderizando comentário:', newComment);
            const mentionRegex = /@([a-zA-Z0-9._]+)/g;
            let rendered = newComment;

            const mentions = newComment.match(mentionRegex) || [];
            console.log('Menções encontradas:', mentions);

            for (const mention of mentions) {
                const username = mention.slice(1);
                console.log('Buscando usuário:', username);
                try {
                    const response = await axios.get(`/users/by-username/${username}`);
                    console.log('Resposta da API:', response.data);
                    const userId = response.data.id;
                    rendered = rendered.replace(
                        mention,
                        `<span class="text-blue-500 cursor-pointer hover:underline" onclick="window.location.href='/users/${userId}'">@${username}</span>`
                    );
                } catch (error) {
                    console.error(`Erro ao buscar ID do usuário ${username}:`, error);
                    rendered = rendered.replace(mention, `<span class="text-blue-500">@${username}</span>`);
                }
            }

            console.log('Comentário renderizado:', rendered);
            return rendered;
        },
        async renderReply(replyContent) {
            console.log('Renderizando resposta:', replyContent);
            const mentionRegex = /@([a-zA-Z0-9._]+)/g;
            let rendered = replyContent;

            const mentions = replyContent.match(mentionRegex) || [];
            console.log('Menções encontradas:', mentions);

            for (const mention of mentions) {
                const username = mention.slice(1);
                console.log('Buscando usuário:', username);
                try {
                    const response = await axios.get(`/users/by-username/${username}`);
                    console.log('Resposta da API:', response.data);
                    const userId = response.data.id;
                    rendered = rendered.replace(
                        mention,
                        `<span class="text-blue-500 cursor-pointer hover:underline" onclick="window.location.href='/users/${userId}'">@${username}</span>`
                    );
                } catch (error) {
                    console.error(`Erro ao buscar ID do usuário ${username}:`, error);
                    rendered = rendered.replace(mention, `<span class="text-blue-500">@${username}</span>`);
                }
            }

            console.log('Resposta renderizada:', rendered);
            return rendered;
        },
        async detectMention(e) {
            if (this.isAddingMention) return;

            const input = e.target.value;
            const cursorPosition = e.target.selectionStart;
            const mentionIndex = input.lastIndexOf('@', cursorPosition - 1);

            if (mentionIndex !== -1) {
                const afterAt = input.slice(mentionIndex + 1);
                const queryEnd = afterAt.indexOf(' ') !== -1 ? afterAt.indexOf(' ') : afterAt.length;
                const query = afterAt.slice(0, queryEnd);

                const isCompletedMention = query === this.lastMentionQuery && afterAt.includes(' ');
                if (isCompletedMention) {
                    this.showSuggestions = false;
                    this.suggestions = [];
                    return;
                }

                const isActiveMention = cursorPosition > mentionIndex && (queryEnd === afterAt.length || cursorPosition <= mentionIndex + 1 + queryEnd);
                if (!isActiveMention) {
                    this.showSuggestions = false;
                    this.suggestions = [];
                    return;
                }

                if (query.length > 0) {
                    try {
                        const { data } = await axios.get(`/users/search?query=${query}`);
                        this.suggestions = data;
                        this.showSuggestions = data.length > 0;
                    } catch (error) {
                        console.error('Erro ao buscar sugestões:', error);
                        this.showSuggestions = false;
                        this.suggestions = [];
                    }
                } else {
                    this.showSuggestions = false;
                    this.suggestions = [];
                }
            } else {
                this.showSuggestions = false;
                this.suggestions = [];
            }
        },
        async detectReplyMention(e, type) {
            if (this.isAddingMention) return;

            const input = e.target.value;
            const cursorPosition = e.target.selectionStart;
            const mentionIndex = input.lastIndexOf('@', cursorPosition - 1);

            if (mentionIndex !== -1) {
                const afterAt = input.slice(mentionIndex + 1);
                const queryEnd = afterAt.indexOf(' ') !== -1 ? afterAt.indexOf(' ') : afterAt.length;
                const query = afterAt.slice(0, queryEnd);

                const isCompletedMention = query === this.lastReplyMentionQuery && afterAt.includes(' ');
                if (isCompletedMention) {
                    this.showReplySuggestions = false;
                    this.replySuggestions = [];
                    return;
                }

                const isActiveMention = cursorPosition > mentionIndex && (queryEnd === afterAt.length || cursorPosition <= mentionIndex + 1 + queryEnd);
                if (!isActiveMention) {
                    this.showReplySuggestions = false;
                    this.replySuggestions = [];
                    return;
                }

                if (query.length > 0) {
                    try {
                        const { data } = await axios.get(`/users/search?query=${query}`);
                        this.replySuggestions = data;
                        this.showReplySuggestions = data.length > 0;
                    } catch (error) {
                        console.error('Erro ao buscar sugestões de resposta:', error);
                        this.showReplySuggestions = false;
                        this.replySuggestions = [];
                    }
                } else {
                    this.showReplySuggestions = false;
                    this.replySuggestions = [];
                }
            } else {
                this.showReplySuggestions = false;
                this.replySuggestions = [];
            }
        },
        async fetchPost() {
    try {
        const response = await axios.get(`/posts/${window.location.pathname.split('/')[2]}`);
        this.post = response.data;

        this.post.liked = this.post.likes.some(like => like.user_id === this.$page.props.auth.user?.id);
        this.post.likes_count = this.post.likes.length;

        this.post.comments = await Promise.all(this.post.comments.map(async comment => {
            const renderedContent = await this.renderComment(comment.content);
            comment.liked = comment.likes.some(like => like.user_id === this.$page.props.auth.user?.id);
            comment.likes_count = comment.likes.length;

            const replies = await Promise.all((comment.replies || []).map(async reply => {
                const renderedBody = await this.renderReply(reply.body);
                reply.liked = reply.likes.some(like => like.user_id === this.$page.props.auth.user?.id);
                reply.likes_count = reply.likes.length;

                const children = await Promise.all((reply.children || []).map(async child => {
                    const renderedChildBody = await this.renderReply(child.body);
                    child.liked = child.likes.some(like => like.user_id === this.$page.props.auth.user?.id);
                    child.likes_count = child.likes.length;

                    // Verificação de segurança: remover duplicatas em comment.replies
                    const isDuplicateInReplies = comment.replies.some(r => r.id === child.id && r.id !== reply.id);
                    if (isDuplicateInReplies) {
                        console.warn('Resposta aninhada duplicada detectada em comment.replies:', child);
                        comment.replies = comment.replies.filter(r => r.id !== child.id);
                    }

                    return { ...child, renderedBody: renderedChildBody };
                }));

                this.renderedReplies.set(reply.id, renderedBody);
                return { ...reply, renderedBody, children };
            }));

            return { ...comment, renderedContent, replies };
        }));

        console.log('Post carregado:', JSON.parse(JSON.stringify(this.post)));
        console.log('Comentários:', this.post.comments.map(c => ({
            id: c.id,
            content: c.content,
            replies: c.replies.map(r => ({
                id: r.id,
                body: r.body,
                children: r.children.map(ch => ({ id: ch.id, body: ch.body }))
            }))
        })));

        this.formattedDate = moment(this.post.created_at).format('DD/MM/YYYY');
    } catch (error) {
        console.error('Erro ao carregar o post:', error);
        this.post = null;
        Swal.fire({
            icon: 'error',
            title: 'Erro ao carregar o post!',
            text: error.response?.data.message || 'Ops, ocorreu um erro inesperado.',
        });
    }
},
        formatDate(date) {
            return moment(date).locale('pt-BR').format('dddd, D MMMM, YYYY HH:mm:ss A');
        },
        editPost() {
            this.postToEdit = { ...this.post };
            this.showEditModal = true;
            this.showDropdown = false;
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
                            text: error.response?.data.message || 'Ops, ocorreu um erro inesperado.',
                        });
                    }
                }
            });
            this.showDropdown = false;
        },
        async toggleLikePost() {
            try {
                const response = await axios.post(`/posts/${this.post.id}/like`);
                this.post.liked = response.data.liked;
                this.post.likes_count = response.data.likes_count;

                if (this.post.liked) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Você curtiu o post!',
                        showConfirmButton: false,
                        timer: 1500,
                    });
                }
            } catch (error) {
                console.error('Erro ao curtir o post:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Ocorreu um erro ao tentar curtir o post.',
                });
            }
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
                    content: this.newComment,
                    post_id: this.post.id,
                    mentions: this.mentions,
                });
                if (response.data.message === 'Comentário adicionado com sucesso') {
                    const newComment = response.data.data;
                    newComment.renderedContent = await this.renderComment(newComment.content);
                    newComment.replies = [];
                    this.post.comments.push(newComment);
                    Swal.fire({
                        icon: 'success',
                        title: 'Comentário adicionado com sucesso!',
                        showConfirmButton: false,
                        timer: 1500,
                    });
                    this.newComment = '';
                    this.mentions = [];
                    this.lastMentionQuery = '';
                } else {
                    throw new Error('Erro ao adicionar comentário');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro ao adicionar comentário!',
                    text: error.response?.data.message || 'Ops, ocorreu um erro inesperado.',
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
                            timer: 1500,
                        });
                        this.activeCommentDropdown = null;
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro ao excluir comentário!',
                            text: error.response?.data.message || 'Ops, ocorreu um erro inesperado.',
                        });
                    }
                }
            });
        },
        async deleteReply(replyId) {
    Swal.fire({
        icon: 'warning',
        title: 'Deseja excluir esta resposta?',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                // Fazer a requisição para excluir a resposta no backend
                await axios.delete(`/replies/${replyId}`);

                // Encontrar e remover a resposta do estado local
                let updated = false;
                for (let commentIndex = 0; commentIndex < this.post.comments.length; commentIndex++) {
                    const comment = { ...this.post.comments[commentIndex] };

                    // Procurar no nível de replies (respostas diretas)
                    const replyIndex = comment.replies.findIndex(r => r.id === replyId);
                    if (replyIndex !== -1) {
                        // Remover a resposta direta
                        comment.replies.splice(replyIndex, 1);
                        this.post.comments[commentIndex] = comment;
                        updated = true;
                        break;
                    }

                    // Procurar em respostas aninhadas (children)
                    for (let reply of comment.replies) {
                        const childIndex = (reply.children || []).findIndex(child => child.id === replyId);
                        if (childIndex !== -1) {
                            // Remover a resposta aninhada
                            reply.children.splice(childIndex, 1);

                            // Reatribuir o array children para garantir reatividade
                            reply.children = [...reply.children];

                            // Atualizar o comentário no array de comentários do post
                            this.post.comments[commentIndex] = comment;
                            updated = true;
                            break;
                        }
                    }

                    if (updated) break;
                }

                if (!updated) {
                    console.error('Resposta não encontrada para exclusão:', replyId);
                }

                // Log para depuração
                console.log('Estado após exclusão:', JSON.parse(JSON.stringify(this.post.comments)));

                Swal.fire({
                    icon: 'success',
                    title: 'Resposta excluída com sucesso!',
                    showConfirmButton: false,
                    timer: 1500,
                });
                this.activeReplyDropdown = null;
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro ao excluir resposta!',
                    text: error.response?.data.message || 'Ocorreu um erro inesperado.',
                });
            }
        }
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
                this.fetchPost();
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro ao atualizar o post!',
                    text: error.response?.data.message || 'Ops, ocorreu um erro inesperado.',
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
        document.addEventListener('click', this.closeDropdowns); // Ajustado para usar closeDropdowns
    },
    beforeUnmount() {
        document.removeEventListener('click', this.closeDropdowns); // Ajustado para usar closeDropdowns
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

.suggestions-list {
    position: absolute;
    background-color: white;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    max-height: 200px;
    overflow-y: auto;
    z-index: 10;
    width: 100%;
}

.suggestions-list li {
    padding: 8px 12px;
    color: #1f2937;
    cursor: pointer;
}

.suggestions-list li:hover {
    background-color: #f3f4f6;
}

/* Estilos para os comentários */
.comment-container {
    transition: all 0.2s ease;
}

.comment-container:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Estilos para as respostas */
.reply-container {
    position: relative;
    transition: all 0.2s ease;
}

.reply-container:hover {
    background-color: #f9fafb;
}

/* Estilos para as respostas aninhadas */
.child-reply-container {
    position: relative;
    transition: all 0.2s ease;
}

.child-reply-container:hover {
    background-color: #f1f5f9;
}

/* Ajuste de espaçamento e tipografia */
.text-gray-700 {
    line-height: 1.6;
}

/* Estilo para o botão de responder */
button i {
    margin-right: 4px;
}
</style>
