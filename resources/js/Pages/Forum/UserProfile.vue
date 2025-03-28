<!-- resources/js/Pages/Forum/UserProfile.vue -->
<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Header from '@/Components/Header.vue';
import moment from 'moment';
import 'moment/dist/locale/pt-br';

const page = usePage();

// Acessar os dados diretamente de page.props
const user = computed(() => page.props.user);
const posts = computed(() => page.props.posts);

const formatRelativeTime = (date) => {
  return moment(date).fromNow();
};

const getDescriptionPreview = (description) => {
  const plainText = description.replace(/<[^>]+>/g, '');
  return plainText.length > 100 ? plainText.slice(0, 100) + '...' : plainText;
};
</script>

<template>
  <div class="bg-neutral-100 min-h-screen">
    <Header />

    <!-- Conteúdo Principal -->
    <div class="flex flex-1 pl-4 pr-4 md:pl-40 md:pr-40">
      <!-- Perfil do Usuário -->
      <div class="w-full md:w-1/4 container p-6">
        <div class="bg-white p-6 rounded-lg shadow-md">
          <div class="flex items-center space-x-4">
            <img
              :src="user?.profile_photo_url || 'https://via.placeholder.com/80'"
              :alt="user?.name"
              class="w-20 h-20 rounded-full object-cover"
            />
            <div>
              <h2 class="text-xl font-bold text-gray-800">{{ user?.name || 'Usuário' }}</h2>
              <p class="text-sm text-gray-600">Membro desde: {{ user?.created_at ? formatRelativeTime(user.created_at) : 'N/A' }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Lista de Posts do Usuário -->
      <div class="flex-1 p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Posts de {{ user?.name || 'Usuário' }}</h2>
        <ul v-if="posts && posts.length > 0" class="space-y-6">
          <li
            v-for="post in posts"
            :key="post.id"
            @click="$inertia.get(`/posts/${post.id}/edit`)"
            class="cursor-pointer p-6 bg-white rounded-lg shadow-md hover:bg-yellow-50 hover:-translate-y-1 transition-shadow"
          >
            <div class="flex justify-between items-start mb-4">
              <h3 class="text-xl font-semibold text-gray-800">
                {{ post.title && post.title.length > 50 ? post.title.slice(0, 50) + '...' : post.title || 'Sem título' }}
              </h3>
              <div class="flex items-center space-x-2">
                <span
                  v-for="tag in post.tags"
                  :key="tag.id"
                  :style="{ backgroundColor: tag.color }"
                  class="inline-block px-2 py-1 text-sm text-white rounded"
                  style="text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6);"
                >
                  <i :class="tag.icon" class="text-sm text-white"></i>
                  {{ tag.name }}
                </span>
                <div class="flex items-center">
                  <i class="fa-solid fa-comment text-gray-400"></i>
                  <span class="text-sm text-gray-400 pl-1">{{ post.comments_count || 0 }}</span>
                </div>
              </div>
            </div>
            <p class="text-sm text-gray-600 mb-4" v-html="getDescriptionPreview(post.description)"></p>
            <p class="text-sm text-gray-500">
              Publicado em: {{ post.created_at ? formatRelativeTime(post.created_at) : 'N/A' }}
            </p>
          </li>
        </ul>
        <div v-else class="text-gray-500">
          Este usuário ainda não criou nenhum post.
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.description-preview {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.5em;
  max-height: 3em;
}
</style>
