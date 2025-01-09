<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
      <div class="bg-white rounded shadow p-6 w-full max-w-lg">
        <h2 class="text-xl font-semibold mb-4">Criar Novo Post</h2>
        <div class="mb-4">
          <label for="title" class="block text-sm font-medium text-gray-700">Título</label>
          <input
            v-model="newPost.title"
            id="title"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            required
          />
        </div>
        <div class="mb-4">
          <label for="description" class="block text-sm font-medium text-gray-700">Descrição</label>
          <textarea
            v-model="newPost.description"
            id="description"
            rows="4"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            required
          ></textarea>
        </div>
        <div class="mb-4">
          <button
            @click="openTaggingModal"
            class="py-2 px-4 bg-gray-500 text-white rounded hover:bg-gray-600">
            Escolha as Tags
          </button>
        </div>

        <div v-if="newPost.tags.length > 0" class="mb-4">
          <h3 class="font-medium text-gray-700">Tags Selecionadas:</h3>
          <ul class="list-disc list-inside">
            <li v-for="(tag, index) in newPost.tags" :key="index">
              {{ tag?.name }}
            </li>
          </ul>
        </div>

        <div class="flex justify-end space-x-4">
          <button
            @click="$emit('close')"
            class="py-2 px-4 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
            Cancelar
          </button>
          <button
            @click="submitPost"
            class="py-2 px-4 bg-indigo-500 text-white rounded hover:bg-indigo-600">
            Criar Post
          </button>
        </div>

        <tagging-modal
          v-if="showTaggingModal"
          @close="closeTaggingModal"
          @select-tags="setTags"
          :selected-tags="newPost.tags"
        />
      </div>
    </div>
  </template>

  <script>
  import TaggingModal from './TaggingModal.vue';
  import Swal from 'sweetalert2'; // Importando o SweetAlert2
  import axios from 'axios';

  export default {
    components: { TaggingModal },
    data() {
      return {
        newPost: {
          title: '',
          description: '',
          tags: [],
        },
        showTaggingModal: false,
      };
    },
    methods: {
      openTaggingModal() {
        this.showTaggingModal = true;
      },
      closeTaggingModal() {
        this.showTaggingModal = false;
      },
      setTags(tags) {
        this.newPost.tags = tags.map(tag => ({
          id: parseInt(tag.id, 10),
          name: tag.name || '',
          code: tag.code || null,
        }));

        console.log('Tags selecionadas:', this.newPost.tags);
      },

      async submitPost() {
        if (this.newPost.tags && this.newPost.tags.length) {
          const tagIds = this.newPost.tags.map(tag => tag.id);

          if (tagIds.length === 0) {
            console.error('Nenhuma tag válida selecionada');
            return;
          }

          const postPayload = {
            title: this.newPost.title,
            description: this.newPost.description,
            tags: tagIds,
          };

          try {
            // Envia a solicitação ao servidor com Inertia
            await this.$inertia.post(route('posts.store'), postPayload);

            // Exibe a mensagem de sucesso com SweetAlert2
            Swal.fire({
              icon: 'success',
              title: 'Post criado com sucesso!',
              showConfirmButton: false,
              timer: 1500,
            });

            // Fechar o modal
            this.$emit('close');  // Emitir evento para fechar o modal
            this.clearForm();  // Limpar os campos do formulário após o envio
          } catch (error) {
            // Caso haja um erro, você pode exibir uma mensagem de erro
            Swal.fire({
              icon: 'error',
              title: 'Erro ao criar o post!',
              text: error.response?.data.message || 'Ocorreu um erro inesperado.',
            });
          }
        } else {
          console.error('Nenhuma tag selecionada');
        }
      },

      clearForm() {
        this.newPost.title = '';
        this.newPost.description = '';
        this.newPost.tags = [];
      },
    },
  };
  </script>
